<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Utils\UserUtils;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * @param Request $request
     * @param string $token
     * @return Response|Application|ResponseFactory
     */
    public function store(Request $request, string $token): Response|Application|ResponseFactory
    {
        $request->validate([
            'username' => 'required|email',
            'password' => 'required|min:8|confirmed'
        ]);

        $status = Password::reset([
            'email' => $request->get('username'),
            'token' => $token,
            'password' => $request->get('password'),
            'status' => fn($query) => $query->where('status', '!=', UserUtils::Suspended)
        ], function ($user, $password) {
            $user->password = Hash::make($password);
            $user->status = UserUtils::Active; //above check will ensure the user is not suspended
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user)); //notify any observer
        });

        return $status === Password::PASSWORD_RESET ?
            response(['message' => __($status)], 201) :
            response(['message' => 'Failed to set password.' . __($status)], 400);

    }
}
