<?php

namespace App\Http\Controllers\api\Auth;


use App\Http\Controllers\Controller;

use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JwtTokenController extends Controller
{
    /**
     * @param Staff $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Staff $user) : JsonResponse {
        /* @var Staff $user */
        /* Creating a personal access toke for current user. There is some security issues with tokens reuse */
        $token = $user->createToken('DealerPanel')->accessToken;

        return response()->json(['success' => true, 'token' => $token], 200);
    }

    public function logout(Request $request, Staff $user) {
        /* @var Staff $user */
        /* Removing all personal access tokens from the user */
        $user->tokens()->delete();

        return response()->json(['success' => true], 200);
    }
}