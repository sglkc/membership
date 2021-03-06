<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json($users);
    }

    public function profile($id)
    {
        $user = User::with('referrer', 'referrals')->find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }

        return response()->json($user);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'referrer_code' => 'nullable',
        ]);

        $referrer_code = $request->input('referrer_code') ?? null;

        if ($referrer_code) {
            $referrer = User::where('referral_code', $referrer_code)->first();

            if (!$referrer) {
                return response()->json([
                    'message' => 'Referral code invalid',
                ], 404);
            }
        }

        $user = User::create([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'referral_code' => substr(base64_encode(md5(rand())), 5, 5),
            'referrer_code' => $referrer_code,
        ]);

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'Not found',
            ], 404);
        }

        $password = $request->input('password');

        if (Hash::check($user->password, $password)) {
            return response()->json([
                'message' => 'invalid password',
            ], 404);
        }

        return response()->json([
            'message' => 'success',
            $user,
        ]);
    }

    public function delete(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'message' => 'not found',
            ], 404);
        }

        $password = $request->input('password');

        if (Hash::check($user->password, $password)) {
            return response()->json([
                'message' => 'invalid password',
            ], 404);
        }

        $user->delete();

        return response()->json($user);
    }
}
