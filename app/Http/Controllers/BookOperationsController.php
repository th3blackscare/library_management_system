<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookOperationsController extends Controller
{
    public function ListAllBooks(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'title' => 'string',
            'author' => 'string',
            'isbn' => 'string',
        ]);
        if($validator->fails()) return response()->json(['message' => implode($validator->errors()->all())], 400);

        $booksQuery = DB::table('books')->select('id', 'title', 'author', 'isbn', 'avail_quantity', 'shelf_loc')->where('status','!=','deleted');
        /**
         * there is multiple if without else because we have a search criteria
         * the use can search with isbn, title or author or can search with any possible combination of them,
         * so we need to make sure that he can search with any one or any combination. here it comes the logic
         * every if statement will check if the filed is existing in the request, if the filed exist
         * it will add it to the query as 'or where' to make sure that the user can search with any
         * or all fields and get a result in the worst case. if we used ' and where ' it will return a result
         * only if the row has exactly the three fields value.
        **/
        if($request->has('isbn')){
            $booksQuery = $booksQuery->where('isbn','=',$request->isbn);
        }
        /** since the following fields are strings, so we need to search with the like operator.
        * suppose the user is searching with just a part of the book title or author name and he didn't know the full name
        * we need to make sure that he can get a result.
        **/
        if ($request->has('title')){
            $booksQuery = $booksQuery->orWhere('title','like',"%{$request->title}%");
        }
        if($request->has('author')){
            $booksQuery = $booksQuery->orWhere('author','like',"%{$request->author}%");
        }

        /**
         * this query might result a large number of rows, so we need to paginate the results and return a user defined number of rows per page to not abuse the database
         * if the request doesn't contain any per_page field then we will use a default value of 25
         * also this query can result the database server to reach a deadlock state if the table has a large number of rows or consume the web server resources
         **/

        $booksQuery = $booksQuery->paginate($request->per_page ?? 25);

        return response([
            'books' => $booksQuery->items(),
            "current_page" => $booksQuery->currentPage(),
            "last_page" => $booksQuery->lastPage(),
            "total_books" => $booksQuery->total(),
            "per_page" => $booksQuery->perPage(),
        ]);
    }

    public function addBook(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title' => 'required|string',
            'author' => 'required|string',
            'isbn' => 'required|string',
            'quantity' => 'required|int',
            'shelf_loc' => 'required|string',
        ]);
        if($validator->fails()) return response()->json(['message' => implode($validator->errors()->all())], 400);

        $bookData = [
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'avail_quantity' => $request->quantity,
            'shelf_loc' => $request->shelf_loc
        ];

        /**
         * before inserting any data to the database, we need to make sure that we will not have a duplicated data
         * so to make sure that we will not insert a new book that was previously was there. we will check if a book
         * with the isbn is previously was recorder. since the isbn is a unique identifier for the books and
         * can only belong to a one book, we will use it for finding any previous books.
         * if a record exist, we will update its status to available. and increase the quantity with the new quantity.
         */

        /**
         * will use DB transaction to ensure the Integrity and Consistency of the Data
         * we need to make sure that our data remain untouched if anything gone wrong.
         * and not to have a partial data inserted.
        **/
        DB::beginTransaction();
        try {
            $isbnQuery = DB::table('books')->select('id','avail_quantity')->where('isbn','=',$bookData['isbn']);
            if(!empty($isbnQuery->first())){
                // a book exists with the current ISBN so will lock the row to make sure that no one else can update it until this transaction finishes
                // then will update its status and quantity only
                $isbnQuery->lockForUpdate()->update([
                    'avail_quantity' => $isbnQuery->first()->avail_quantity+$bookData['avail_quantity'],
                    'status' => 'available'
                ]);
                $isbnQuery = $isbnQuery->first()->id;
            } else{
                $isbnQuery = DB::table('books')->insertGetId($bookData);
            }
            // if everything done well, then we will commit the changes to the database
            DB::commit();
            return response(['message' => "book inserted with id " . $isbnQuery]);
        } catch (\Exception $e){
            // if anything gone wrong, we need to rollback and drop any changes during the last transactions
            DB::rollBack();
            return response(['message' => $e->getMessage()],400);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|int',
            'title' => 'string',
            'author' => 'string',
            'isbn' => 'string',
            'quantity' => 'int',
            'shelf_loc' => 'string',
        ]);
        if($validator->fails()) return response()->json(['message' => implode($validator->errors()->all())], 400);
        DB::beginTransaction();
        try {
            // i will use multiple if statements to make sure that only the posted data get changed.
            $bookData = [];
            if ($request->has('title')) $bookData['title'] = $request->title;
            if ($request->has('author')) $bookData['author'] = $request->author;
            if ($request->has('isbn')){

                // since the ISBN is unique and cannot be the same for more than one book, we need to make sure that the user won't the current ISBN
                // to another one that is exists on our database.

                $bookData['isbn'] = $request->isbn;
                $checkISBN = DB::table('books')->select('id')->where('isbn','=',$bookData['isbn'])->where('id','!=',$request->id)->first();
                if (!empty($checkISBN)) return response(['message' => 'the new ISBN is already exists in our database.']);
            }
            if ($request->has('quantity')) $bookData['avail_quantity'] = $request->quantity;
            if ($request->has('shelf_loc')) $bookData['shelf_loc'] = $request->shelf_loc;
            if ($request->has('status')) $bookData['status'] = $request->status;

            DB::table('books')->where('id','=',$request->id)->update($bookData);
            DB::commit();
            return response(['message' => 'update operation success']);
        }catch (\Exception $e){
            DB::rollBack();
            return response(['message' => $e->getMessage()],400);
        }
    }

    public function delete(Request $request)
    {
        $request['status'] = 'deleted';
        return $this->update($request);
    }
}
