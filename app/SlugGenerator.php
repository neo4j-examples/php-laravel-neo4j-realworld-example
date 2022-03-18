<?php

namespace App;

use Illuminate\Support\Str;
use Laudis\Neo4j\Basic\Session;
use function explode;
use function str_replace;

class SlugGenerator
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function generateSlug(string $label, string $string): string
    {
        $slug = Str::slug($string);
        $regex = $slug . '(-\d+)?';

        $result = $this->session->run(<<<CYPHER
        MATCH (node:$label)
        WHERE node.slug =~ \$regex

        RETURN node.slug AS slug
        ORDER BY slug DESC
        LIMIT 1
        CYPHER, ['regex' => $regex]);

        if ($result->isEmpty()) {
            return $slug;
        }

        $existingSlug = $result->getAsCypherMap(0)->getAsString('slug');
        if (str_replace($slug, '', $existingSlug) === '') {
            return $slug . '-1';
        }

        $parts = explode('-', $existingSlug);
        $number = (int) $parts[count($parts) - 1];
        ++$number;

        return $slug . '-' . $number;
    }
}
