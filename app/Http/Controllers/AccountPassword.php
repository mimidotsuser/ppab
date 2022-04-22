<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AccountPassword extends Controller
{

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        Gate::allowIf(fn() => $user->id === Auth::id());

        $request->validate([
            'old_password' => 'required|max:250',
            'password' => 'required|min:8|confirmed'
        ]);

        if (!Hash::check($request->get('old_password'), $user->password)) {
            return response()->noContent(403);
        }

        $user->password = Hash::make($request->get('password'));

        return response(['message' => __('passwords.changed')], 201);
    }

}
