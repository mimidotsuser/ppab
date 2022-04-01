<?php

namespace App\Policies;

use App\Models\RequestForQuotation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RequestForQuotationPolicy
{
    use HandlesAuthorization;

    /**
     * User can search for an RFQ model
     * @param User $user
     * @return mixed
     */
    public function search(User $user)
    {
        return $user->role->permissions->contains('name', 'rfqs.search');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */

    public function viewAny(User $user)
    {
        return $user->role->permissions->contains('name', 'rfqs.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param RequestForQuotation $requestForQuotation
     * @return Response|bool
     */
    public function view(User $user, RequestForQuotation $requestForQuotation)
    {
        return $user->whereRelation('role.permissions', 'name', 'rfqs.view')->exists();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'rfqs.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param RequestForQuotation $requestForQuotation
     * @return Response|bool
     */
    public function update(User $user, RequestForQuotation $requestForQuotation)
    {
        return $user->role->permissions->contains('name', 'rfqs.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param RequestForQuotation $requestForQuotation
     * @return Response|bool
     */
    public function delete(User $user, RequestForQuotation $requestForQuotation)
    {
        return $user->role->permissions->contains('name', 'rfqs.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param RequestForQuotation $requestForQuotation
     * @return Response|bool
     */
    public function restore(User $user, RequestForQuotation $requestForQuotation)
    {
        return $user->role->permissions->contains('name', 'rfqs.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param RequestForQuotation $requestForQuotation
     * @return Response|bool
     */
    public function forceDelete(User $user, RequestForQuotation $requestForQuotation)
    {
        return $user->role->permissions->contains('name', 'rfqs.delete');
    }
}
