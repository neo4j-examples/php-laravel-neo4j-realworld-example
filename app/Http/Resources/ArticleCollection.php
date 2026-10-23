<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ArticleCollection extends ResourceCollection
{
    public static $wrap = 'articles';

    public $collects = ArticleResource::class;

    /**
     * @param Request $request
     */
    public function paginationInformation($request, array $paginated, array $default): array
    {
        return ['articlesCount' => $paginated['total']];
    }
}
