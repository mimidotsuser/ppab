<?php

namespace App\Policies;

use App\Models\ProductItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ProductItemPolicy
{
    use HandlesAuthorization;

    /**
     * User can search product item model
     * @param User $user
     * @return Response|bool
     */
    public function search(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'productItems.search');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'productItems.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ProductItem $productItem
     * @return Response|bool
     */
    public function view(User $user, ProductItem $productItem)
    {
        return $user->role->permissions->contains('name', 'productItems.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'productItems.create');

    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ProductItem $productItem
     * @return Response|bool
     */
    public function update(User $user, ProductItem $productItem): Response|bool
    {
        return $user->role->permissions->contains('name', 'productItems.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ProductItem $productItem
     * @return Response|bool
     */
    public function delete(User $user, ProductItem $productItem): Response|bool
    {
        return $user->role->permissions->contains('name', 'productItems.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ProductItem $productItem
     * @return Response|bool
     */
    public function restore(User $user, ProductItem $productItem): Response|bool
    {
        return $user->role->permissions->contains('name', 'productItems.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ProductItem $productItem
     * @return Response|bool
     */
    public function forceDelete(User $user, ProductItem $productItem): Response|bool
    {
        return $user->role->permissions->contains('name', 'productItems.delete');
    }
}
