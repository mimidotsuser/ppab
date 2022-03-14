<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\UserUtils;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function store(Request $request): Response|Application|ResponseFactory
    {

        $request->validate(['username' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink(['email' => $request->get('username'),
            'status' => fn($query) => $query->where('status', '!=', UserUtils::Suspended)]);

        return $status == Password::RESET_LINK_SENT ?
            response(['message' => __($status)], 201) :
            response(['message' => 'Failed to send password reset link.' . __($status)], 400);
    }
}
