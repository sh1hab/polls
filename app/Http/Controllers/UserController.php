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
                'full_name' =>'required|min:6',
                'username'  =>'required|unique:users|min:6',
                'email'     =>'required|email|unique:users|min:6',
                'password'  =>'required|min:6',
            ]);
        }catch (ValidationException $e) {
            return $this->response('validation error',$e->getMessage(),403);
        }

        try{
            $id = app('db')->table('users')->insertGetId([
                'full_name' =>  trim($request->input('full_name')),
                'username'  =>  strtolower(trim($request->input('username'))),
                'email'     =>  strtolower(trim($request->input('email'))),
                'password'  =>  app('hash')->make($request->input('password')),
            ]);

            $user = app('db')
                ->table('users')
                ->select('username','email','password')
                ->where('id',$id)
                ->first();

            return $this->response("Data Insert successful",json_encode($user),201);

        }catch (\PDOException $e) {
            return $this->response("database error","",500);
        }

    }

    function response($message,$data,$status=200){
        return response()->json(
            array(
                'status'=>$status,
                'message'=>$message,
                'data'=>$data
            ),$status
        );
    }

}
