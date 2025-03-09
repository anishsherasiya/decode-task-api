<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{    
    /**
     * User register
     *
     * @param  mixed $request
     * @return void
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|regex:/[a-z]/|regex:/[0-9]/',
            'c_password' => 'required|same:password',
            'profile_image' => 'nullable|image|mimes:jpg,png|max:2048',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'profile_image' => $request->file('profile_image')?->store('profiles', 'public'),
            'status' => !empty($request->status) ? 'active' : $request->status,
        ]); 
        
        $response['token'] =  $user->createToken('de')->plainTextToken;
        $response['user'] =  $user;
        return $this->sendResponse($response, 'User register successfully.');
    }

   
    /**
     * User login
     *
     * @param  mixed $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user();
            if ($user->status !== "active") {
                return $this->sendError('Your account temporarily blocked. Please contact to admin.', ['error'=>'Unauthorised']);
            }   
            $success['token'] =  $user->createToken('de')->plainTextToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User login successfully.');
        } else { 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }
}
