<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{    
    /**
     * getUser
     *
     * @return void
     */
    public function getUser()
    {
        $response['user'] =  Auth::user();
        return $this->sendResponse($response, 'User fetched successfully.');
    }
    
    /**
     * logout
     *
     * @param  mixed $request
     * @return void
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse('', 'User Logged Out successfully.');
    }
}
