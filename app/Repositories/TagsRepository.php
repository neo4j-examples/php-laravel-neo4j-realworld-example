<?php

namespace App\Repositories;

use App\Models\Tag;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\Node;
use function compact;

class TagsRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    public function addTag(string $slug, string $tag): void
    {
        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug})
        WITH a
        MERGE (t:Tag {name: $tag})
        MERGE (a) - [:TAGGED] -> (t)
        CYPHER, compact('slug', 'tag'));
    }

    public function getTags(array $slugs): array
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (article:Article) - [:TAGGED] -> (tag:Tag)
        WHERE article.slug IN $slugs
        RETURN apoc.map.fromPairs([article.slug, tag]) AS tags
        CYPHER, ['slugs' => $slugs])->getAsCypherMap(0)
            ->getAsCypherMap('tags')
            ->map(static fn (Node $node) => new Tag($node->getProperty('name')))
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
