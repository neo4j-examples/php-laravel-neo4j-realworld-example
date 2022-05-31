<?php

namespace App\Http\Resources;

use App\Models\Article;
use App\Models\Article;
use Illuminate\Http\Resources\Json\JsonResource;
use const DATE_ATOM;

/**
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    public static $wrap = 'article';

    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'body' => $this->body,
            'description' => $this->description,
            'createdAt' => $this->createdAt->toDateTime()->format(DATE_ATOM),
            'updatedAt' => $this->updatedAt->toDateTime()->format(DATE_ATOM),

            'tagList' => TagResource::collection($this->tags),
            'author' => UserResource::collection($this->author),
            'favorited' => $this->favorited,
            'favoriteCount' => $this->favoriteCount
        ];
    }
}
