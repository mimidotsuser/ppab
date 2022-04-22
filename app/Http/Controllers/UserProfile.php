<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserProfile extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return User[]
     */
    public function show(User $user)
    {
        Gate::allowIf(fn() => $user->id === Auth::id());

        $user->load('role');

        return ['data' => $user];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return User[]
     */
    public function update(Request $request, User $user): array
    {
        Gate::allowIf(fn() => $user->id === Auth::id());

        $request->validate([
            'first_name' => 'required|max:250',
            'last_name' => 'nullable|max:250',
        ]);

        $user->first_name = $request->get('first_name');
        $user->last_name = $request->get('last_name');

        $user->update();

        return ['data' => $user];
    }
}
