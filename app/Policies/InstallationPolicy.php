<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Installation;
use Illuminate\Auth\Access\HandlesAuthorization;

class InstallationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_installation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Installation $installation): bool
    {
        return $user->can('view_installation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_installation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Installation $installation): bool
    {
        return $user->can('update_installation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Installation $installation): bool
    {
        return $user->can('delete_installation');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_installation');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Installation $installation): bool
    {
        return $user->can('force_delete_installation');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_installation');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Installation $installation): bool
    {
        return $user->can('restore_installation');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_installation');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Installation $installation): bool
    {
        return $user->can('replicate_installation');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_installation');
    }
}
