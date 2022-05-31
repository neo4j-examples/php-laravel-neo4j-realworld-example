<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class CannotFollowSelf implements Rule
{
    public function passes($attribute, $value): bool
    {
        return !User::query()
            ->where($attribute, $value)
            ->where($attribute, auth()->user()->$attribute)
            ->exists();
    }

    public function message(): string
    {
        return 'A user cannot follow itself';
    }
}
