<?php

namespace App\Policies;

use App\Models\ReceiptNoteVoucher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ReceiptNoteVoucherPolicy
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
        return $user->role->permissions->contains('name', 'receiptNoteVoucher.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return Response|bool
     */
    public function view(User $user, ReceiptNoteVoucher $receiptNoteVoucher)
    {
        return $user->role->permissions->contains('name', 'receiptNoteVoucher.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->role->permissions->contains('name', 'receiptNoteVoucher.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return Response|bool
     */
    public function update(User $user, ReceiptNoteVoucher $receiptNoteVoucher)
    {
        return $user->role->permissions->contains('name', 'receiptNoteVoucher.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return Response|bool
     */
    public function delete(User $user, ReceiptNoteVoucher $receiptNoteVoucher)
    {
        return $user->role->permissions->contains('name', 'receiptNoteVoucher.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return Response|bool
     */
    public function restore(User $user, ReceiptNoteVoucher $receiptNoteVoucher)
    {
        return $user->role->permissions->contains('name', 'receiptNoteVoucher.edit');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ReceiptNoteVoucher $receiptNoteVoucher
     * @return Response|bool
     */
    public function forceDelete(User $user, ReceiptNoteVoucher $receiptNoteVoucher)
    {
        return $user->role->permissions->contains('name', 'receiptNoteVoucher.delete');
    }
}
