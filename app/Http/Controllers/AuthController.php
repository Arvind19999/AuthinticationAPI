<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;
// use Carbon\Factory;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Support\Facades\Hash;
use JWTAuth;
use JWTFactory;
// use Lcobucci\JWT\Token
class AuthController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    public function testing(Request $request){
        print_r($request->all());
        return response()->json([
            'status'=>'success',
            'mgs'=>'You are good to go'
        ]);
        // return "hi there";
    }

    public function register(Request $request){
        $v = Validator::make($request->all(), [
            'name'=>'required|string|min:3|max:10',
            'email'=>'required|string|email|max:65|unique:users',
            'password'=>'required|confirmed|min:5|max:20',
            'password_confirmation'=>'required',
            'phone_number'=>'required|min:10|max:10|unique:users'
        ]);
        if($v->fails()){
            return response()->json([
                'status'=>'Error',
                'Error'=>$v->errors()
            ],422);
        }
        else{
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->phone_number = $request->phone_number;
            $user->save();
            return response()->json([
                'status'=>'success',
                'message'=>'User created Successfully'
            ],200);
        }
    }

    public function login(Request $request){
        $v = Validator::make($request->all(), [
            'phone_number'=>'required|min:10|max:10',
            'password'=>'required'
        ]);
        if($v->fails()){
            return response()->json([
                'status'=>'Error',
                'Error'=>$v->errors()
            ]);
        }
        $credentials = $request->only('phone_number', 'password');
        if (! $token = Auth::guard('api')->attempt($credentials)){
            return response()->json([
                'status'=>'error',
                'Message'=>'Unathorized Access',
                'token'=>$token
            ],200);
        }

        return $this->CreateNewToken($token);
    }
    public function CreateNewToken($token){
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth('api')->factory()->getTTL()*60,
            'user'=>auth()->user()
        ]);

    }

    public function profile(){
        return response()->json([auth()->user()]);
    }

    public function logout(){
        auth()->logout();
        return response()->json([
            'status'=>'Success',
            'message'=>'User LoggedOut Successfully'
        ],200);
    }
}
