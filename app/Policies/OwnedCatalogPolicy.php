<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OwnedCatalogPolicy
{
    public function update(User $user, Model $model): bool
    {
        return (int) $model->owner_user_id === (int) $user->id;
    }

    public function delete(User $user, Model $model): bool
    {
        return (int) $model->owner_user_id === (int) $user->id;
    }

    public function view(User $user, Model $model): bool
    {
        return (int) $model->owner_user_id === 0 || (int) $model->owner_user_id === (int) $user->id;
    }
}
