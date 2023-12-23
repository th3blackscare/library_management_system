<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookOperationsController extends Controller
{
    public function ListAllBooks(Request $request)
    {
        $booksQuery = DB::table('books')->select('id', 'title', 'author', 'isbn', 'avail_quantity', 'shelf_loc');
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
         */
        $booksQuery = $booksQuery->paginate($request->per_page ?? 25);
        return response([
            'books' => $booksQuery->items(),
            "current_page" => $booksQuery->currentPage(),
            "last_page" => $booksQuery->lastPage(),
            "total_books" => $booksQuery->total(),
            "per_page" => $booksQuery->perPage(),
        ]);
    }
}
