<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\InvitationService;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    public function invite(Request $request, InvitationService $service)
    {
        $request->validate([
            'email' => 'required|email',
            'role' => 'required|string'
        ]);

        $invite = $service->invite(
            $request->user(),
            $request->email,
            $request->role
        );

        return response()->json([
            'message' => 'Invitation sent',
            'token' => $invite->token
        ]);
    }

    public function accept(Request $request, InvitationService $service)
    {
        $request->validate([
            'token' => 'required',
            'name' => 'required',
            'password' => 'required|min:6'
        ]);

        $user = $service->accept(
            $request->token,
            $request->name,
            $request->password
        );

        Auth::login($user);

        return response()->json([
            'message' => 'Account created and logged in',
            'user' => $user
        ]);
    }
}
