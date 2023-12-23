<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BorrowerController extends Controller
{
    public function list()
    {
        $listUsersOnly = DB::table('borrowers')->select('id','name','email','phone','created_at')->where('status','=','1')->get();
        return response(['users'=>$listUsersOnly]);
    }

    public function add(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:borrowers',
            'phone' => 'required|string|unique:borrowers',
        ]);

        if($validation->fails()) return response()->json(['message' => implode($validation->errors()->all())], 400);

        $user = DB::table('borrowers')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'phone'=> $request->phone
        ]);

        return response(['message'=>"User added successfully with id {$user}"]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'id' => 'required|int',
            'name' => 'string',
            'email' => 'string',
            'phone' => 'string',
        ]);
        if($validator->fails()) return response()->json(['message' => implode($validator->errors()->all())], 400);
        DB::beginTransaction();
        try {
            // i will use multiple if statements to make sure that only the posted data get changed.
            $userData = [];
            if ($request->has('name')) $userData['name'] = $request->name;
            if ($request->has('email')){
                $userData['email'] = $request->email;
                $checkEmail = DB::table('borrowers')->select('id')->where('email','=',$userData['email'])->where('id','!=',$request->id)->first();
                if (!empty($checkEmail)) return response(['message' => 'the new email is already exists in our database.']);
            }
            if ($request->has('phone')){
                $userData['phone'] = $request->phone;
                $checkPhone = DB::table('borrowers')->select('id')->where('phone','=',$userData['phone'])->where('id','!=',$request->id)->first();
                if (!empty($checkPhone)) return response(['message' => 'the new phone is already exists in our database.']);
            }
            if ($request->has('status')) $userData['status'] = $request->status;

            DB::table('borrowers')->where('id','=',$request->id)->update($userData);
            DB::commit();
            return response(['message' => 'update operation success']);
        }catch (\Exception $e){
            DB::rollBack();
            return response(['message' => $e->getMessage()],400);
        }
    }

    public function delete(Request $request)
    {
        $request['status'] = 0;
        return $this->update($request);
    }
}
