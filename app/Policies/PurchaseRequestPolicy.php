<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PurchaseRequestPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @return bool
     */
    public function viewAnyPendingVerification(User $user): bool
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.verify');
    }

    /**
     * @param User $user
     * @param PurchaseRequest $purchaseRequest
     * @return bool
     */
    public function verify(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->created_by_id !== $user->id && //cannot verify their own request
            $user->role->permissions->contains('name', 'purchaseRequests.verify');
    }

    /**
     * @param User $user
     * @return bool
     */
    public function viewAnyPendingApproval(User $user): bool
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.approve');
    }

    /**
     * @param User $user
     * @param PurchaseRequest $purchaseRequest
     * @return bool
     */
    public function approve(User $user, PurchaseRequest $purchaseRequest): bool
    {
        return $purchaseRequest->created_by_id !== $user->id && //cannot approve their own request
            $user->role->permissions->contains('name', 'purchaseRequests.approve');
    }

    /**
     * User can search purchase request models
     * @param User $user
     * @return Response|bool
     */
    public function search(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.search');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param PurchaseRequest $purchaseRequest
     * @return Response|bool
     */
    public function view(User $user, PurchaseRequest $purchaseRequest)
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param PurchaseRequest $purchaseRequest
     * @return Response|bool
     */
    public function update(User $user, PurchaseRequest $purchaseRequest)
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param PurchaseRequest $purchaseRequest
     * @return Response|bool
     */
    public function delete(User $user, PurchaseRequest $purchaseRequest)
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param PurchaseRequest $purchaseRequest
     * @return Response|bool
     */
    public function restore(User $user, PurchaseRequest $purchaseRequest)
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param PurchaseRequest $purchaseRequest
     * @return Response|bool
     */
    public function forceDelete(User $user, PurchaseRequest $purchaseRequest)
    {
        return $user->role->permissions->contains('name', 'purchaseRequests.delete');
    }
}
