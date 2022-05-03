<?php

namespace App\Presenters;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use function array_map;
use const DATE_ATOM;

class ArticleJSONPresenter
{
    public function __construct(private readonly UserJSONPresenter $userPresenter)
    {
    }

    public function presentArticle(Article $article): array
    {
        return [
            'title' => $article->title,
            'slug' => $article->slug,
            'body' => $article->body,
            'description' => $article->description,
            'createdAt' => $article->createdAt->format(DATE_ATOM),
            'updatedAt' => $article?->updatedAt->format(DATE_ATOM)
        ];
    }

    public function presentFullArticle(Article $article, User $author, bool $following, array $tags, bool $favorited, int $favoriteCount): array
    {
        $article = $this->presentArticle($article);

        $article['tagList'] = array_map(static fn (Tag $tag) => $tag->value, $tags);
        $article['author'] = $this->userPresenter->presentAsProfile($author, $following);
        $article['favorited'] = $favorited;
        $article['favoritesCount'] = $favoriteCount;

        return $article;
    }

    /**
     * @param array<int, Article> $articles
     * @param int $count
     * @param array<string, Tag> $tagMap
     * @param array<string, User> $authorMap
     * @param array<string, int> $favoriteCountMap
     * @param array<string, bool> $favoritedMap
     * @param array<string, bool> $followingMap
     *
     * @return array
     */
    public function presentFullArticles(array $articles, int $count, array $tagMap, array $authorMap, array $favoriteCountMap, array $favoritedMap, array $followingMap): array
    {
        $tbr = [];
        foreach ($articles as $article) {
            $author = $authorMap[$article->slug];
            $following = $followingMap[$author->username] ?? false;
            $tags = $tagMap[$article->slug] ?? [];
            $favoriteCount = $favoriteCountMap[$article->slug] ?? 0;
            $favorited = $favoritedMap[$article->slug] ?? false;

            $tbr[] = $this->presentFullArticle($article, $author, $following, $tags, $favorited, $favoriteCount);
        }

        return [
            'articles' => $tbr,
            'articlesCount' => $count
        ];
    }
}
