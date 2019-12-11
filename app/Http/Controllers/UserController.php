<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    function create(Request $request)
    {
        try{
            $this->validate($request,[
                'full_name'=>'required',
                'username'=>'required|unique:users',
                'email'=>'required',
                'password'=>'required',
            ]);
        }catch (ValidationException $e)
        {
            return response()->json(
                [
                    'success'=>false,
                    'status'=>200,
                    'message'=>$e->getMessage()
                ]
            );
        }

        $id = DB::table('users')->insertGetId([
            'full_name' =>  $request->input('full_name'),
            'username'  =>  $request->input('username'),
            'email'     =>  $request->input('email'),
            'password'  =>  $request->input('password'),
        ]);

        return response()->json(
            [
                'status'=>200,
                'message'=>'Data Insert successful'
            ],422
        );
    }

}
