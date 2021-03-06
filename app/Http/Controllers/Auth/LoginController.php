<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Utils\UserUtils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{


    /**
     * Authenticate user with username and password combination.
     * @param Request $request
     * @return JsonResponse User profile if successful or error
     */
    public function authenticate(Request $request): JsonResponse
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

            return response()->json(['data' => User::with(['role:name,id',
                'role.permissions:name,id'])
                ->select(['first_name', 'last_name', 'id', 'updated_at','role_id'])
                ->findOrFail(Auth::id())
            ]);
        }
        return response()
            ->json(['errors' => ['message' => 'Invalid username/password combination']], 401);
    }


    /**
     * Invalidate user session
     * @param Request $request
     * @return Response No content
     */
    public function logout(Request $request): Response
    {

        \auth()->logout();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerate();
        return response()->noContent();
    }
}
