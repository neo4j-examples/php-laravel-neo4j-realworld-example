<?php

namespace App\Repositories;

use App\Models\Comment;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;

class CommentRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    /**
     * @return list<Comment>
     */
    public function getComments(string $slug): array
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}) <- [:COMMENTED_ON] - (comment:Comment)
        RETURN comment
        CYPHER, [ 'slug' => $slug ]);

        return $this->mapResults($result);
    }

    public function comment(string $articleSlug, string $body, string $username): Comment
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}), (author:User {username: $username})
        CREATE (a) <- [:COMMENTED_ON] - (comment:Comment {id: 0, createdAt: datetime(), updatedAt: datetime(), body: $body}) <- [:AUTHORED] - (author)
        RETURN comment
        CYPHER, [
            'slug' => $articleSlug,
            'username' => $username,
            'body' => $body
        ]);

        return $this->mapResults($result)[0];
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

    /**
     * @return list<Comment>
     */
    private function mapResults(SummarizedResult $result): array
    {
        return $result->map(static function (CypherMap $map) {
            $comment = $map->getAsNode('comment')->getProperties();
            return new Comment(
                id: $comment->getAsInt('id'),
                createdAt: $comment->getAsDateTime('createdAt')->toDateTime(),
                updatedAt: $comment->getAsDateTime('updatedAt')->toDateTime(),
                body: $comment->getAsString('body')
            );
        })->toArray();
    }
}
