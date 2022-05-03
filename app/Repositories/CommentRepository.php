<?php

namespace App\Repositories;

use App\Models\Comment;
use App\Models\User;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;

class CommentRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    /**
     * @return list<array{comment: Comment, author: User, following: bool}>
     */
    public function getComments(string $articleSlug, string $username): array
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}) <- [:COMMENTED_ON] - (comment:Comment) <- [:AUTHORED] - (author:User)
        OPTIONAL MATCH (u:User {username: $username}) - [:FOLLOWS] -> (author)
        RETURN comment, author, u IS NOT NULL AS following
        CYPHER, [
            'slug' => $articleSlug,
            'username' => $username,
        ]);

        return $this->mapResults($result);
    }

    /**
     * @return list<array{comment: Comment, author: User, following: bool}>
     */
    public function comment(string $articleSlug, string $body, string $username): array
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}), (author:User {username: $username})
        CREATE (a) <- [:COMMENTED_ON] - (comment:Comment {id: 0, createdAt: datetime(), updatedAt: datetime(), body: $body}) <- [:AUTHORED] - (author)
        RETURN comment, author, false AS following
        CYPHER, [
            'slug' => $articleSlug,
            'username' => $username,
            'body' => $body
        ]);

        return $this->mapResults($result);
    }

    public function uncomment(string $articleSlug, string $username, int $id): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}) <- [:COMMENTED_ON] - (c:Comment {id: $id}) <- [:AUTHORED] - (u:User {username: $username})
        DETACH DELETE c
        CYPHER, [
            'slug' => $articleSlug,
            'username' => $username,
            'id' => $id
        ]);
    }

    private function mapResults(SummarizedResult $result): array
    {
        return $result->map(static function (CypherMap $map) {
            return [
                'comment' => Comment::fromArray($map->getAsNode('comment')->getProperties()->toArray()),
                'author' => User::fromArray($map->getAsNode('author')->getProperties()->toArray()),
                'following' => $map->getAsBool('following')
            ];
        })->toArray();
    }
}
