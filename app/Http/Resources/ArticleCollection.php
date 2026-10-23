<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;

class ArticleCollection extends ResourceCollection
{
    public static $wrap = 'articles';

    public $collects = ArticleResource::class;

    public function toArray($request): array
    {
        return $this->collection
            ->map(static fn (ArticleResource $article) => Arr::except($article->toArray($request), 'body'))
            ->all();
    }

    public function paginationInformation($request, array $paginated, array $default): array
    {
        return ['articlesCount' => $paginated['total']];
    }
}
