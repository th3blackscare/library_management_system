<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BooksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'user',
            'email' => 'user',
            'password'=> Hash::make('123456789')
        ]);
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
