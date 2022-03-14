<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->permissions->contains('name', 'organization.customers.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Customer $customer
     * @return Response|bool
     */
    public function view(User $user, Customer $customer)
    {
        return $user->role->permissions->contains('name', 'organization.customers.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'organization.customers.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Customer $customer
     * @return Response|bool
     */
    public function update(User $user, Customer $customer)
    {
        return $user->role->permissions->contains('name', 'organization.customers.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Customer $customer
     * @return Response|bool
     */
    public function delete(User $user, Customer $customer)
    {
        return $user->role->permissions->contains('name', 'organization.customers.delete');

    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Customer $customer
     * @return Response|bool
     */
    public function restore(User $user, Customer $customer)
    {
        return $user->role->permissions->contains('name', 'organization.customers.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Customer $customer
     * @return Response|bool
     */
    public function forceDelete(User $user, Customer $customer)
    {
        return $user->role->permissions->contains('name', 'organization.customers.delete');
    }
}
