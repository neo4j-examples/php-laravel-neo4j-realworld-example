<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class ProfileResource extends JsonResource
{
    public static $wrap = 'profile';

    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'username' => $this->username,
            'bio' => $this->bio,
            'image' => $this->image,
            'following' => $this->followers()->where('username', auth()->id())->exists()
        ];
    }
}
