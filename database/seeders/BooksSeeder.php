<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i=0;$i<160;$i++) {
            DB::table('books')->insert([
                "title" => "Book ".$i+1,
                "author" => 'Book Author '.$i+1,
                "isbn" => '0-000-00-'.$i+1,
                "avail_quantity" => '10',
                "shelf_loc" => '1001'
            ]);
        }
    }
}
