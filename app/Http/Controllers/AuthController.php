<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $userLogin = User::where([
            'account_name' => $request->account_name,
            'password_php' => md5($request->password_php)
        ])->first();

        if (!$userLogin) {
            return response([
                'message' => ['These credentials do not match our records.']
            ], 404);
        } else {
            // Auth::user();
            $tokenData = $userLogin->createToken('MyApiToken');
            $token = $tokenData->accessToken;
            $expiration = $tokenData->token->expires_at->diffInSeconds(Carbon::now());

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => $expiration
            ]);
        }
    }

    public function loginGrant(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'account_name' => 'required',
        //     'password_php' => 'required'
        // ]);

        // if ($validator->fails()) {
        //     return response()->json($validator->errors(), 400);
        // }

        // $baseUrl = url('http://localhost:8002');
        $baseUrl = url('/');
        // print_r($baseUrl);die;
        $response = Http::post("{$baseUrl}/oauth/token", [
            'account_name' => ($request->account_name),
            'password_php' => md5($request->password_php),
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'grant_type' => 'password'
        ]);

        $result = json_decode($response->getBody(), true);
        if (!$response->ok()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // return $response;
        return response()->json($result);
    }

    public function profile()
    {
        return Auth::user();
    }
}
