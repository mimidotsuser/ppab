<?php

namespace App\Policies;

use App\Models\MaterialRequisition;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MaterialRequisitionPolicy
{
    use HandlesAuthorization;

    public function search(User $user): bool
    {
        return $user->role->permissions->contains('name', 'materialRequisition.search');
    }

    public function viewAnyPendingVerification(User $user): bool
    {
        return $user->role->permissions->contains('name', 'materialRequisition.verify');
    }

    public function verify(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $materialRequisition->created_by_id !== $user->id && //cannot verify their own request
            $user->role->permissions->contains('name', 'materialRequisition.verify');
    }

    public function viewAnyPendingApproval(User $user): bool
    {
        return $user->role->permissions->contains('name', 'materialRequisition.approve');
    }

    public function approve(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $materialRequisition->created_by_id !== $user->id && //cannot approve their own request
            $user->role->permissions->contains('name', 'materialRequisition.approve');
    }

    public function issue(User $user, MaterialRequisition $materialRequisition): bool
    {
        return $user->role->permissions->contains('name', 'checkout.create');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->role->permissions->contains('name', 'materialRequisition.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param MaterialRequisition $materialRequisition
     * @return Response|bool
     */
    public function view(User $user, MaterialRequisition $materialRequisition)
    {
        return $user->role->permissions->contains('name', 'materialRequisition.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'materialRequisition.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MaterialRequisition $materialRequisition
     * @return Response|bool
     */
    public function update(User $user, MaterialRequisition $materialRequisition)
    {
        return $user->role->permissions->contains('name', 'materialRequisition.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MaterialRequisition $materialRequisition
     * @return Response|bool
     */
    public function delete(User $user, MaterialRequisition $materialRequisition)
    {
        return $user->role->permissions->contains('name', 'materialRequisition.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MaterialRequisition $materialRequisition
     * @return Response|bool
     */
    public function restore(User $user, MaterialRequisition $materialRequisition)
    {
        return $user->role->permissions->contains('name', 'materialRequisition.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MaterialRequisition $materialRequisition
     * @return Response|bool
     */
    public function forceDelete(User $user, MaterialRequisition $materialRequisition)
    {
        return $user->role->permissions->contains('name', 'materialRequisition.delete');
    }
}
