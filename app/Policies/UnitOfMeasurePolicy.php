<?php

namespace App\Policies;

use App\Models\UnitOfMeasure;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UnitOfMeasurePolicy
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
        return $user->role->permissions->contains('name', 'uom.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param UnitOfMeasure $unitOfMeasure
     * @return Response|bool
     */
    public function view(User $user, UnitOfMeasure $unitOfMeasure)
    {
        return $user->role->permissions->contains('name', 'uom.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'uom.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UnitOfMeasure $unitOfMeasure
     * @return Response|bool
     */
    public function update(User $user, UnitOfMeasure $unitOfMeasure)
    {
        return $user->role->permissions->contains('name', 'uom.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UnitOfMeasure $unitOfMeasure
     * @return Response|bool
     */
    public function delete(User $user, UnitOfMeasure $unitOfMeasure)
    {
        return $user->role->permissions->contains('name', 'uom.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param UnitOfMeasure $unitOfMeasure
     * @return Response|bool
     */
    public function restore(User $user, UnitOfMeasure $unitOfMeasure)
    {
        return $user->role->permissions->contains('name', 'uom.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param UnitOfMeasure $unitOfMeasure
     * @return Response|bool
     */
    public function forceDelete(User $user, UnitOfMeasure $unitOfMeasure)
    {
        return $user->role->permissions->contains('name', 'uom.delete');
    }
}
