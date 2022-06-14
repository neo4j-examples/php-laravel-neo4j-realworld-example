<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use function response;

class UserController extends Controller
{
    public function login(Request $request): UserResource|JsonResponse
    {
        $request->validate([
            'user.email' => 'required|email:rfc',
            'user.password' => 'required|max:255'
        ]);

        $credentials = $request->json('user');
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || !Hash::check($credentials['password'], $user->getAttribute('passwordHash'))) {
            return response()->json('Email or password is incorrect')->setStatusCode(422);
        }

        return new UserResource($user);
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'user.email' => 'required|email:rfc',
            'user.username' => 'required|string|unique:User,email|min:3|max:50',
            'user.password' => ['required', ...$this->passwordValidationRules()]
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
            'user.email' => 'email:rfc',
            'user.username' => 'required|string|unique:User,email|min:3|max:50',
            'user.password' => $this->passwordValidationRules()
        ]);

        $requestedUser = $request->json('user');

        $user = User::query()->findOrFail($requestedUser['username']);

        $this->authorize('update', $user);

        $values = Arr::only($requestedUser, ['email', 'image', 'bio']);
        if (array_key_exists('password', $requestedUser)) {
            $values['passwordHash'] = Hash::make($requestedUser['password']);
        }

        $user->update($values);

        return new UserResource($user);
    }

    /**
     * @return array
     */
    private function passwordValidationRules(): array
    {
        return [
            'string',
            'max:100',
            Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
        ];
    }
}
