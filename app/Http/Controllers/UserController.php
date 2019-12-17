<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTAuth;

class UserController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

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
                'created' => Carbon::now()
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
        try{
            $this->validate($request, array(
                'email'     =>'required|email|min:6',
                'password'  =>'required|min:6',
            ));
        }catch (ValidationException $e) {
            return $this->response('validation error',$e->getMessage(),403);
        }

        var_dump( Auth::guard('api')->attempt( $request->only('email','password') ) );
            //
            //        die();

            //        app('auth')->authenticate($request->all());

        $user = app('db')
            ->table('users')
            ->where('email',$request->input('email'))
            ->andWhere('password',$request->input('password'))
            ->first();
    }

    

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {

            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }

        return response()->json(compact('token'));
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
