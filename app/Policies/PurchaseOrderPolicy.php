<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PurchaseOrderPolicy
{
    use HandlesAuthorization;

    /**
     *
     * @param User $user
     * @return mixed
     */
    public function search(User $user)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.search');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param PurchaseOrder $purchaseOrder
     * @return Response|bool
     */
    public function view(User $user, PurchaseOrder $purchaseOrder)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param PurchaseOrder $purchaseOrder
     * @return Response|bool
     */
    public function update(User $user, PurchaseOrder $purchaseOrder)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param PurchaseOrder $purchaseOrder
     * @return Response|bool
     */
    public function delete(User $user, PurchaseOrder $purchaseOrder)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param PurchaseOrder $purchaseOrder
     * @return Response|bool
     */
    public function restore(User $user, PurchaseOrder $purchaseOrder)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param PurchaseOrder $purchaseOrder
     * @return Response|bool
     */
    public function forceDelete(User $user, PurchaseOrder $purchaseOrder)
    {
        return $user->role->permissions->contains('name', 'purchaseOrders.delete');
    }
}
