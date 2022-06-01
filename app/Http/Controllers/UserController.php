<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function login(Request $request): UserResource
    {
        $request->validate([
            'user.email' => 'required|email:rfc',
            'user.password' => 'required|max:255|current_password'
        ]);

        $credentials = $request->json('user');

        /** @var User $user */
        $user = User::query()->where('email', $credentials['email'])->firstOrFail();

        return new UserResource($user);
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'user.email' => 'required|email:rfc',
            'user.username' => 'required|string|unique:User,email|min:3|max:50',
            'user.password' => 'required|string|max:100'
        ]);

        $user = $request->json('user');
        $user['passwordHash'] = Hash::make($user['password']);

        $user = User::query()->create(Arr::only($user, ['email', 'username', 'passwordHash']));

        return (new UserResource($user))
            ->toResponse($request)
            ->setStatusCode(201);
    }

    public function get(): UserResource
    {
        return new UserResource(Auth::user());
    }

    public function update(Request $request): UserResource
    {
        $request->validate([
            'user.email' => 'required|email:rfc',
            'user.username' => 'required|string|unique:User,email|min:3|max:50',
            'user.password' => 'required|string|max:100'
        ]);

        $requestedUser = $request->json('user');

        $user = User::query()->findOrFail($requestedUser['username']);

        $this->authorize('update', $user);

        $user->update(Arr::only($requestedUser, ['email', 'username', 'image', 'bio']));

        return new UserResource($user);
    }
}
