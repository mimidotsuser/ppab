<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ProductPolicy
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
        return $user->role->permissions->contains('name', 'inventory.products.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Product $product
     * @return Response|bool
     */
    public function view(User $user, Product $product): Response|bool
    {
        return $user->role->permissions->contains('name', 'inventory.products.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'inventory.products.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Product $product
     * @return Response|bool
     */
    public function update(User $user, Product $product): Response|bool
    {
        return $user->role->permissions->contains('name', 'inventory.products.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Product $product
     * @return Response|bool
     */
    public function delete(User $user, Product $product): Response|bool
    {
        return $user->role->permissions->contains('name', 'inventory.products.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Product $product
     * @return Response|bool
     */
    public function restore(User $user, Product $product): Response|bool
    {
        return $user->role->permissions->contains('name', 'inventory.products.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Product $product
     * @return Response|bool
     */
    public function forceDelete(User $user, Product $product): Response|bool
    {
        return $user->role->permissions->contains('name', 'inventory.products.delete');
    }
}
