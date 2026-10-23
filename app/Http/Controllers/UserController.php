<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use function array_key_exists;
use function response;

class UserController extends Controller
{
    public function login(Request $request): UserResource|JsonResponse
    {
        $request->validate([
            'user.email' => 'required|string',
            'user.password' => 'required|string|max:255'
        ]);

        $credentials = $request->json('user');
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || !Hash::check($credentials['password'], $user->getAttribute('passwordHash'))) {
            return response()->json(['errors' => ['credentials' => ['invalid']]], 401);
        }

        return new UserResource($user);
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'user.email' => 'required|email:rfc|max:255',
            'user.username' => 'required|string|min:3|max:50',
            'user.password' => ['required', ...$this->passwordValidationRules()]
        ]);

        $user = $request->json('user');
        $this->ensureUnique($user);
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
            'user.email' => 'sometimes|required|email:rfc|max:255',
            'user.username' => 'sometimes|required|string|min:3|max:50',
            'user.password' => ['sometimes', 'required', ...$this->passwordValidationRules()],
            'user.bio' => 'sometimes|nullable|string|max:10000',
            'user.image' => 'sometimes|nullable|string|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $requestedUser = $request->json('user', []);
        $this->ensureUnique($requestedUser, $user);

        $values = Arr::only($requestedUser, ['email', 'username', 'image', 'bio']);
        if (array_key_exists('password', $requestedUser)) {
            $values['passwordHash'] = Hash::make($requestedUser['password']);
        }

        $user->update($values);

        return new UserResource($user);
    }

    /**
     * @throws ValidationException when another user already has the requested username or email.
     */
    private function ensureUnique(array $values, ?User $self = null): void
    {
        $errors = [];
        foreach (['username', 'email'] as $field) {
            if (!array_key_exists($field, $values) || $values[$field] === $self?->getAttribute($field)) {
                continue;
            }

            if (User::query()->where($field, $values[$field])->exists()) {
                $errors[$field] = 'has already been taken';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors)->status(409);
        }
    }

    private function passwordValidationRules(): array
    {
        return ['string', 'min:8', 'max:255'];
    }
}
