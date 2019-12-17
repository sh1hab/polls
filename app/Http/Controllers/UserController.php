<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    
    function  index(){
        try{
            $users = app('db')
                ->table('users')
                ->select('id','username','email','password')
                ->paginate(10);

            return $this->response("Data",($users),201);

        }catch (\PDOException $e) {
            return $this->response("database error","",500);
        }


    }

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
                'created_at' => Carbon::now()
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

    function authenticate(Request $request)
    {
        // try{
        //     $this->validate($request, array(
        //         'email'     =>'required|email|min:6',
        //         'password'  =>'required|min:6',
        //     ));
        // }catch (ValidationException $e) {
        //     return $this->response('validation error',$e->getMessage(),403);
        // }

        $validator = Validator::make($request->only('email','password'),array(
            'email'     =>'required|email|min:6',
            'password'  =>'required|min:6',
        ));
        
        if ($validator->fails()) {
            return $this->response('validation error',$validator->errors(),403);
        }

        $token = Auth::guard('api')->attempt( $request->only('email','password') );

        if ( $token ) {
            return $this->response('User Authenticated',$token,200);
        }
        return $this->response('User unuthenticated',$token,400);
            
    }

    protected function profile()
    {
        $user = app('auth')->user();

        if ($user) 
        {
            return $this->response("User Profile found",json_encode($user),201);
        }

        return $this->response('User Profile not found',$token,400);

    }

    function response($message,$data,$status=200)
    {
        return response()->json(
            array(
                'status'=>$status,
                'message'=>$message,
                'data'=>$data
            ),$status
        );
    }

}
