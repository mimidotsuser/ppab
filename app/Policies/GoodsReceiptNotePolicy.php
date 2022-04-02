<?php

namespace App\Policies;

use App\Models\GoodsReceiptNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GoodsReceiptNotePolicy
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
        return $user->role->permissions->contains('name', 'goodsReceiptNote.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return Response|bool
     */
    public function view(User $user, GoodsReceiptNote $goodsReceiptNote)
    {
        return $user->role->permissions->contains('name', 'goodsReceiptNote.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'goodsReceiptNote.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return Response|bool
     */
    public function update(User $user, GoodsReceiptNote $goodsReceiptNote)
    {
        return $user->role->permissions->contains('name', 'goodsReceiptNote.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return Response|bool
     */
    public function delete(User $user, GoodsReceiptNote $goodsReceiptNote)
    {
        return $user->role->permissions->contains('name', 'goodsReceiptNote.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return Response|bool
     */
    public function restore(User $user, GoodsReceiptNote $goodsReceiptNote)
    {
        return $user->role->permissions->contains('name', 'goodsReceiptNote.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param GoodsReceiptNote $goodsReceiptNote
     * @return Response|bool
     */
    public function forceDelete(User $user, GoodsReceiptNote $goodsReceiptNote)
    {
        return $user->role->permissions->contains('name', 'goodsReceiptNote.delete');
    }
}
