<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\UserUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{


    /**
     * Authenticate user with username and password combination.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse User profile if successful or error
     */
    public function authenticate(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        $credentials = ['email' => $validated['username'], 'password' => $validated['password'],
            'status' => function ($query) {
                $query->where('status', '!=', UserUtils::Suspended);
            }];

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            return response()->json(['data' => ['user' => User::with('role')
                ->findOrFail(Auth::id())
            ]]);
        }
        return response()
            ->json(['errors' => ['message' => 'Invalid username/password combination']]);
    }


    /**
     * Invalidate user session
     * @param Request $request
     * @return \Illuminate\Http\Response No content
     */
    public function logout(Request $request): \Illuminate\Http\Response
    {

        \auth()->logout();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerate();
        return response()->noContent();
    }
}
