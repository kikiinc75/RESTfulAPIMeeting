<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use JWTAuthException;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        $cridentials = [
            'email' => $email,
            'password' => $password
        ];

        if ($user->save()) {

            $token = null;
            try {
                if (!$token = JWTAuth::attempt($cridentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect'
                    ], 404);
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                    'msg' => 'Failed_to_create_token'
                ], 404);
            }

            $user->signin = [
                'href' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email,password'
            ];
            $response = [
                'msg' => 'User Created',
                'user' => $user,
                'token' => $token
            ];
            return response()->json($response, 201);
        }

        $response = [
            'msg' => 'An error occured'
        ];

        return response()->json($response, 404);
    }

    public function signin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');


        if ($user = User::where('email', $email)->first()) {
            $cridentials = [
                'email' => $email,
                'password' => $password
            ];

            $token = null;
            try {
                if (!$token = JWTAuth::attempt($cridentials)) {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect'
                    ], 404);
                }
            } catch (JWTAuthException $e) {
                return response()->json([
                    'msg' => 'Failed_to_create_token'
                ], 404);
            }

            $response = [
                'msg' => 'User signin',
                'user' => $user,
                'token' => $token
            ];
            return response()->json($response, 201);
        }

        $response = [
            'msg' => 'An error occurred'
        ];

        return response()->json($response, 404);
    }
}
