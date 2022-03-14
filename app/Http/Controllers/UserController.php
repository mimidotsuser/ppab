<?php

namespace App\Http\Controllers;

use App\Notifications\UserInviteNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Utils\UserUtils;
use App\Models\User;
use JetBrains\PhpStorm\ArrayShape;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return
     */
    public function index(Request $request): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {

        $meta = $this->queryMeta(['created_at', 'first_name', 'last_name'], ['role', 'createdBy']);

        return User::search($request->search)
            ->query(fn(Builder $query) => $query->with($meta->limit))
            ->query(function ($query) use ($meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, 'page', $meta->page);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreUserRequest $request
     * @return
     */
    #[ArrayShape(['data' => "\App\Models\User"])]
    public function store(StoreUserRequest $request): array
    {
        $request->validated();

        $user = new User;
        $user->first_name = $request->get('first_name');
        $user->last_name = $request->get('last_name');
        $user->email = $request->get('email');
        $user->role_id = $request->get('role_id');
        $user->status = UserUtils::PendingActivation;
        $user->password = Hash::make(Str::random(12));

        $user->save();
        $user->load(['role', 'createdBY']);

        //send account setup invite notification
        $token = Password::createToken($user);

        Notification::send($user, new UserInviteNotification($user, $token));

        return ['data' => $user];
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\User $user
     */
    #[ArrayShape(['data' => "\App\Models\User"])]
    public function show(User $user): array
    {
        return ['data' => $user];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateUserRequest $request
     * @param \App\Models\User $user
     * @return array['data'=> "\App\Models\User"]
     */
    #[ArrayShape(['data' => "\App\Models\User"])]
    public function update(UpdateUserRequest $request, User $user): array
    {
        $user->first_name = $request->get('first_name') ?? $user->first_name;
        $user->last_name = $request->get('last_name') ?? $user->last_name;
        $user->email = $request->get('email') ?? $user->email;
        $user->role_id = $request->get('role_id') ?? $user->role_id;
        $user->status = $request->get('role_id') ?? $user->status;

        $user->save();
        $user->load(['createdBy', 'role']);

        return ['data' => $user];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user): \Illuminate\Http\Response
    {
        $user->delete();

        return response()->noContent();
    }
}