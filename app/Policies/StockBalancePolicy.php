<?php

namespace App\Policies;

use App\Models\StockBalance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class StockBalancePolicy
{
    use HandlesAuthorization;

    public function search(User $user)
    {
        return $user->role->permissions->contains('name', 'stockBalances.search');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->permissions->contains('name', 'stockBalances.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param StockBalance $stockBalance
     * @return Response|bool
     */
    public function view(User $user, StockBalance $stockBalance)
    {
        return $user->role->permissions->contains('name', 'stockBalances.view');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param StockBalance $stockBalance
     * @return Response|bool
     */
    public function update(User $user, StockBalance $stockBalance)
    {
        return $user->role->permissions->contains('name', 'stockBalances.edit');
    }

}
