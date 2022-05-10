<?php

namespace App\Policies;

use App\Models\Worksheet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class WorksheetPolicy
{
    use HandlesAuthorization;

    /**
     * User can search product item model
     * @param User $user
     * @return Response|bool
     */
    public function search(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'worksheets.search');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'worksheets.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Worksheet $worksheet
     * @return Response|bool
     */
    public function view(User $user, Worksheet $worksheet)
    {
        return $user->role->permissions->contains('name', 'worksheets.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->role->permissions->contains('name', 'worksheets.create');

    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Worksheet $worksheet
     * @return Response|bool
     */
    public function update(User $user, Worksheet $worksheet): Response|bool
    {
        return $user->role->permissions->contains('name', 'worksheets.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Worksheet $worksheet
     * @return Response|bool
     */
    public function delete(User $user, Worksheet $worksheet): Response|bool
    {
        return $user->role->permissions->contains('name', 'worksheets.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Worksheet $worksheet
     * @return Response|bool
     */
    public function restore(User $user, Worksheet $worksheet): Response|bool
    {
        return $user->role->permissions->contains('name', 'worksheets.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Worksheet $worksheet
     * @return Response|bool
     */
    public function forceDelete(User $user, Worksheet $worksheet): Response|bool
    {
        return $user->role->permissions->contains('name', 'worksheets.delete');
    }
}
