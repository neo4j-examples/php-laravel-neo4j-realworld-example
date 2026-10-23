<?php

namespace App\Http\Resources;

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
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'body' => $this->body,
            'tagList' => $this->tags->pluck('name'),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'favorited' => $this->favorited,
            'favoritesCount' => $this->favoriteCount,
            'author' => new ProfileResource($this->author),
        ];
    }
}
