<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\UserUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function store(Request $request)
    {

        $request->validate(['username' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink(['email' => $request->get('username'),
            'status' => fn($query) => $query->where('status', '!=', UserUtils::Suspended)]);

        return $status == Password::RESET_LINK_SENT ?
            response(['message' => __($status)], 201) :
            response(['message' => 'Failed to send password reset link.' . __($status)], 400);
    }
}
