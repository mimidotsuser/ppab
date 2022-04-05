<?php

namespace App\Policies;

use App\Models\InspectionNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class InspectionNotePolicy
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
        return $user->role->permissions->contains('name', 'inspectionNote.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param InspectionNote $inspectionNote
     * @return Response|bool
     */
    public function view(User $user, InspectionNote $inspectionNote)
    {
        return $user->role->permissions->contains('name', 'inspectionNote.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'inspectionNote.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param InspectionNote $inspectionNote
     * @return Response|bool
     */
    public function update(User $user, InspectionNote $inspectionNote)
    {
        return $user->role->permissions->contains('name', 'inspectionNote.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param InspectionNote $inspectionNote
     * @return Response|bool
     */
    public function delete(User $user, InspectionNote $inspectionNote)
    {
        return $user->role->permissions->contains('name', 'inspectionNote.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param InspectionNote $inspectionNote
     * @return Response|bool
     */
    public function restore(User $user, InspectionNote $inspectionNote)
    {
        return $user->role->permissions->contains('name', 'inspectionNote.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param InspectionNote $inspectionNote
     * @return Response|bool
     */
    public function forceDelete(User $user, InspectionNote $inspectionNote)
    {
        return $user->role->permissions->contains('name', 'inspectionNote.delete');
    }
}
