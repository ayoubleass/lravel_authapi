<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    


    public function register(Request $request){
            $data = Validator::make($request->all(),[
                "name"     => ['required','string','min:2','max:255'],
                "email"    => ['required','email','unique:App\Models\User,email,'],
                "password" => ['required','string','min:8','max:28','confirmed']
            ]);
            if($data->fails()){
                return response()->json([
                    'message' => $data->errors()->first()
                ]);
            }
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' =>  bcrypt($data['password'])  
            ]);
            $token = $user->createToken('SECRET_KEY')->plainTextToken;
            return response([
                'message' => 'user has been registred succesffly',
                'user' => $user,
                'token' => $token
            ],200);
       
    }



    public function login(Request $request){
        $data = $request->validate([
            "email"    => ['required','email'],
            "password" => ['required','string','min:8','max:28']
        ]);
        $user =  User::where('email',$data['email'])->first();
        if(!$user){
        return response(
            [
                'message' => sprintf('user whith %s address is not found',$data['email'])
            ],401
        );
        }
        if(!Hash::check($data['password'],$user->password)){
            return response(['message' => 'no user with this password is found'],401);
        }   
        $token = $user->createToken('SECRET_KEY')->plainTextToken;
        return response([
            'user' => $user,
            'token' => $token
        ],200);
    }

        
    

    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'user succesfully logout']);
    }




}
