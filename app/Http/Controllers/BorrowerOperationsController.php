<?php

namespace App\Http\Controllers;

use App\Exports\BorrowsList;
use App\Exports\OverdueList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Exception;
use function PHPUnit\Framework\isEmpty;

class BorrowerOperationsController extends Controller
{
    public function myBooks(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|int',
        ]);
        if($validator->fails()) return response()->json(['message' => implode($validator->errors()->all())], 400);

        $getCurrentBorrowerBooks = DB::table('borrowed_books')
            ->select('borrowed_books.*','books.title as book_name','books.author as book_author','books.isbn as book_isbn')
            ->join('books','books.id','=','borrowed_books.book')
            ->where('borrowed_books.borrower','=',$request->id)
            ->where('borrowed_books.state','!=','returned')
            ->get();

        return response(["books" => $getCurrentBorrowerBooks]);
    }

    public function returnBook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'operation_id' => 'required|int',
            'borrower_id' => 'required|int',
        ]);
        if ($validator->fails()) return response()->json(['message' => implode($validator->errors()->all())], 400);
        DB::beginTransaction();
        try {
            /**
             * in our returning process we need to make sure that the borrower is returning a book that is actually he had,
             * so the request must contain both operation_id (the row id *primary key*) and the borrower id (the borrower foreign key in the table)
             * our logic will update the operation row in the borrowed_books to set the state as 'returned'
             * and will increase the book quantity by 1 and change the book status from deleted to active
             **/

            $updateBorrowOperationStatus = DB::table('borrowed_books')
                ->select('*')
                ->where('id', '=', $request->operation_id)
                ->where('borrower', '=', $request->borrower_id);

            if(isEmpty($updateBorrowOperationStatus->first())) return response(['message' => 'this book is not in your books'],400);

            if($updateBorrowOperationStatus->first()->state == 'returned') return response(['message' => 'this book is already return'],400);

            $updateBorrowOperationStatus->update(['state' => 'returned']);

            $updateBookStatusAndQuantity = DB::table('books')
                ->where('id', '=', $updateBorrowOperationStatus->first()->book);
            $updateBookStatusAndQuantity->increment('quantity');
            $updateBookStatusAndQuantity->update(['status' => 'available']);
            DB::commit();
            return response(['message' => 'return operation success']);
        }
        catch (\Exception $e){
            DB::rollBack();
            return response(['message' => $e->getMessage()],400);
        }
    }

    public function borrow(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'book' => 'required|int',
            'borrower' => 'required|int',
            'due_date' => 'date'
        ]);
        if($validator->fails()) return response()->json(['message' => implode($validator->errors()->all())], 400);
        DB::beginTransaction();
        try {
            /** we need to make sure that the required book quantity is not 0 because we won't our inventory quantity to be lower than zero,
            * and will lock the row for update to make sure that no one else can update it during the current transaction
            **/
            $checkForBookAvailability = DB::table('books')->where('id','=',$request->book)->lockForUpdate();
            if($checkForBookAvailability->first()->avail_quantity < 1) return response(['message' => 'the availability of the book you requested is less than 1.'],400);

            // if not then we will decrease the availability by one
            $checkForBookAvailability->decrement('avail_quantity');

            /** then we will create a row in the borrowed_books table to keep track the borrows and their books, also to track the due dates
             * we will check for due date in the request, if it's not present in the request then we will assume at as 15 days from now
            **/
            if(!$request->has('due_date')) $request->due_date = Carbon::now()->addDays(15);

            $borrowProcess = DB::table('borrowed_books')->insertGetId([
                'book' => $request->book,
                'borrower' => $request->borrower,
                'due_date' => $request->due_date
            ]);
            DB::commit();
            return response(['message'=>"the borrowing operation success with id $borrowProcess"]);
        } catch (\Exception $e){
            DB::rollBack();
            return response(['message' => $e->getMessage()],400);
        }
    }

    // this function will run as a CRON jon to periodically check the overdue books
    public function trackAndMarkOverdue()
    {
        // the following query will search for any book that has not been returned, and the due_date is less than the day date
        // if any row found, it's state will be changed to overdue and the overdue column value will be changed to true '1'.
        DB::table('borrowed_books')
            ->whereDate('due_date', '<', now())
            ->where('state', 'borrowed')
            ->update(['state' => 'overdue', 'overdue' => 1]);
        echo 'function called';
    }

    public function listOverdueBooks(Request $request)
    {
        // this query will return the overdue books and it's information, also the borrower name
        $overDueList = DB::table('borrowed_books')
            ->select('borrowed_books.*','books.title as book_name','books.author as book_author','books.isbn as book_isbn','borrowers.name as borrowers_name')
            ->join('books','books.id','=','borrowed_books.book')
            ->join('borrowers','borrowers.id','=','borrowed_books.borrower')
            ->where('borrowed_books.state','=','overdue')->paginate($request->per_page ?? 25);

        return response([
            'books' => $overDueList->items(),
            "current_page" => $overDueList->currentPage(),
            "last_page" => $overDueList->lastPage(),
            "total_books" => $overDueList->total(),
            "per_page" => $overDueList->perPage(),
        ]);
    }
    public function exportLastMonthOverdue()
    {
        return Excel::download(new OverdueList, 'last_month_over_due.xlsx');
    }

    public function exportLastMonthBorrowers()
    {
        return Excel::download(new BorrowsList, 'last_month_over_due.xlsx');
    }
}
