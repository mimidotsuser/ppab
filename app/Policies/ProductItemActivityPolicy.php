<?php

namespace App\Policies;

use App\Models\ProductItem;
use App\Models\ProductItemActivity;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ProductItemActivityPolicy
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
        return $user->role->permissions->contains('name', 'productItemActivity.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ProductItemActivity $productItemActivity
     * @return Response|bool
     */
    public function view(User $user, ProductItemActivity $productItemActivity)
    {
        return $user->role->permissions->contains('name', 'productItemActivity.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'productItemActivity.create');

    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ProductItemActivity $productItemActivity
     * @return Response|bool
     */
    public function update(User $user, ProductItemActivity $productItemActivity)
    {
        return $user->role->permissions->contains('name', 'productItemActivity.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ProductItemActivity $productItemActivity
     * @return Response|bool
     */
    public function delete(User $user, ProductItemActivity $productItemActivity)
    {
return true;    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ProductItemActivity $productItemActivity
     * @return Response|bool
     */
    public function restore(User $user, ProductItemActivity $productItemActivity)
    {
        return $user->role->permissions->contains('name', 'productItemActivity.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ProductItemActivity $productItemActivity
     * @return Response|bool
     */
    public function forceDelete(User $user, ProductItemActivity $productItemActivity)
    {
        return $user->role->permissions->contains('name', 'productItemActivity.delete');
    }
}
