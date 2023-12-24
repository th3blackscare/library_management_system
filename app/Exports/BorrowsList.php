<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BorrowsList implements FromCollection,WithHeadings
{
    public function collection()
    {
        $overDueList = DB::table('borrowed_books')
            ->select('borrowed_books.id','borrowers.name as borrowers_name','borrowers.email as email','borrowers.phone as phone','books.title as book_name','books.isbn as book_isbn')
            ->join('books','books.id','=','borrowed_books.book')
            ->join('borrowers','borrowers.id','=','borrowed_books.borrower')
            ->whereDate('borrowed_books.created_at', '=', Carbon::now()->subMonth()->toDate())
            ->get();
        return $overDueList->collect();
    }

    public function headings(): array
    {
        return [
            ['id', 'Borrower Name','Email','Phone','Book','ISBN'],
        ];
    }
}
