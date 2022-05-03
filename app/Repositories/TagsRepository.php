<?php

namespace App\Repositories;

use App\Models\Tag;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\Node;
use function compact;

class TagsRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    public function addTags(string $slug, array $tagList): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (article:Article {slug: $slug})
        UNWIND $tagList AS tag
        MERGE (t:Tag {name: tag})
        WITH article, t
        MERGE (article) - [:TAGGED] -> (t)
        CYPHER, compact('slug', 'tagList'));
    }

    public function getTags(array $slugs): array
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (article:Article) - [:TAGGED] -> (tag:Tag)
        WHERE article.slug IN $slugs
        WITH article.slug AS slug, collect(tag) AS tags
        RETURN apoc.map.fromPairs(collect([slug, tags])) AS tags
        CYPHER, ['slugs' => $slugs])
            ->getAsCypherMap(0)
            ->getAsCypherMap('tags')
            ->map(static function (CypherList $nodes) {
                return $nodes->map(static fn (Node $node) => new Tag($node->getProperty('name')))->toArray();
            })
            ->toArray();
    }

    public function removeTag(string $slug, string $tag): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (article:Article) - [t:TAGGED] -> (tag:Tag)
        WHERE article.slug = $slug AND tag.name = $tag
        DELETE t
        CYPHER,  compact('slug', 'tag'));
    }
}
