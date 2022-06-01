<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class ProfileController extends Controller
{
    public function getProfile(User $user): ProfileResource
    {
        return new ProfileResource($user);
    }

    public function followProfile(User $user): ProfileResource
    {
        $this->authorize('follow', $user);

        $user->followers()->attach(Auth::id());

        return new ProfileResource($user);
    }

    public function unfollowProfile(User $user): ProfileResource
    {
        $user->followers()->detach(Auth::id());

        return new ProfileResource($user);
    }
}
