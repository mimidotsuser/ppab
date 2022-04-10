<?php

namespace App\Policies;

use App\Models\CustomerContract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CustomerContractPolicy
{
    use HandlesAuthorization;

    /**
     * User can search customer models
     * @param User $user
     * @return Response|bool
     */
    public function search(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'customerContracts.search');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->permissions->contains('name', 'customerContracts.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param CustomerContract $customerContract
     * @return Response|bool
     */
    public function view(User $user, CustomerContract $customerContract)
    {
        return $user->role->permissions->contains('name', 'customerContracts.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {

        return $user->role->permissions->contains('name', 'customerContracts.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param CustomerContract $customerContract
     * @return Response|bool
     */
    public function update(User $user, CustomerContract $customerContract)
    {
        return $user->role->permissions->contains('name', 'customerContracts.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param CustomerContract $customerContract
     * @return Response|bool
     */
    public function delete(User $user, CustomerContract $customerContract)
    {
        return $user->role->permissions->contains('name', 'customerContracts.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param CustomerContract $customerContract
     * @return Response|bool
     */
    public function restore(User $user, CustomerContract $customerContract)
    {
        return $user->role->permissions->contains('name', 'customerContracts.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param CustomerContract $customerContract
     * @return Response|bool
     */
    public function forceDelete(User $user, CustomerContract $customerContract)
    {
        return $user->role->permissions->contains('name', 'customerContracts.delete');
    }
}
