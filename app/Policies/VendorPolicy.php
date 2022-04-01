<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class VendorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'vendors.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Vendor $vendor
     * @return Response|bool
     */
    public function view(User $user, Vendor $vendor): Response|bool
    {
        return $user->role->permissions->contains('name', 'vendors.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'vendors.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Vendor $vendor
     * @return Response|bool
     */
    public function update(User $user, Vendor $vendor): Response|bool
    {
        return $user->role->permissions->contains('name', 'vendors.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Vendor $vendor
     * @return Response|bool
     */
    public function delete(User $user, Vendor $vendor): Response|bool
    {
        return $user->role->permissions->contains('name', 'vendors.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Vendor $vendor
     * @return Response|bool
     */
    public function restore(User $user, Vendor $vendor): Response|bool
    {
        return $user->role->permissions->contains('name', 'vendors.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Vendor $vendor
     * @return Response|bool
     */
    public function forceDelete(User $user, Vendor $vendor): Response|bool
    {
        return $user->role->permissions->contains('name', 'vendors.delete');
    }
}
