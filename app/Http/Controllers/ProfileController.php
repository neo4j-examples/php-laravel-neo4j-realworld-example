<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use function auth;

class ProfileController extends Controller
{
    public function getProfile(User $user): ProfileResource
    {
        return new ProfileResource($user);
    }

    public function followProfile(User $user): ProfileResource
    {
        $user->followers()->attach(auth()->id());

        return new ProfileResource($user);
    }

    public function unfollowProfile(User $user): ProfileResource
    {
        $user->followers()->detach(auth()->id());

        return new ProfileResource($user);
    }
}
