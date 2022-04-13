<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\DateTime;
use function auth;
use function response;
use const DATE_ATOM;

class CommentController extends Controller
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getComments(Request $request): JsonResponse
    {
        $parameters = [
            'slug' => $request->get('slug'),
            'email' => optional(auth()->user())->email
        ];

        $result = $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}) <- [:COMMENTED_ON] - (comment:Comment) <- [:AUTHORED] - (author:User)
        OPTIONAL MATCH (u:User {email: $email}) - [:FOLLOWS] -> (author)
        RETURN comment, author, u IS NOT NULL AS following
        CYPHER, $parameters);

        return $this->commentResponseFromArray($result);
    }

    public function comment(Request $request): JsonResponse
    {
        /** @var User|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null) {
            return response()->json()->setStatusCode(401);
        }

        $parameters = [
            'slug' => $request->get('slug'),
            'comment' => $request->json('comment'),
            'email' => optional(auth()->user())->email
        ];

        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}), (u:User {email: $email})
        CREATE (a) <- [:COMMENTED_ON] - (c:Comment {id: 0, createdAt: datetime(), updatedAt: datetime(), body: comment['body']}) <- [:AUTHORED] - (u)
        CYPHER, $parameters);

        return $this->getComments($request);
    }

    public function uncomment(Request $request): JsonResponse
    {
        /** @var User|null $authenticatable */
        $authenticatable = auth()->user();
        if ($authenticatable === null) {
            return response()->json()->setStatusCode(401);
        }

        $parameters = [
            'slug' => $request->get('slug'),
            'email' => optional(auth()->user())->email,
            'id' => $request->get('id')
        ];

        $this->session->run(<<<'CYPHER'
        MATCH (a:Article {slug: $slug}) <- [:COMMENTED_ON] - (c:Comment {id: $id}) <- [:AUTHORED] - (u:User {email: $email})
        DETACH DELETE c
        CYPHER, $parameters);

        return response()->json();
    }

    private function commentResponseFromArray(CypherList $results): JsonResponse
    {
        $comments = [];
        /** @var CypherMap $result */
        foreach ($results as $result) {
            $author = $result->getAsNode('author')->getProperties();
            $comment = $result->getAsNode('comment')->getProperties();

            /** @var DateTime $dateTime */
            $comments[] = [
                'id' => $comment->get('id', null),
                'createdAt' => optional(optional($comment->get('createdAt', null))->toDateTime())->format(DATE_ATOM),
                'updatedAt' => optional(optional($comment->get('updatedAt', null))->toDateTime())->format(DATE_ATOM),
                'body' => $comment->get('body'),
                'author' => [
                    'username' => $author->get('username'),
                    'bio' => $author->get('bio'),
                    'image' => $author->get('image'),
                    'following' => $result->get('following')
                ]
            ];
        }

        return response()->json(['comments' => $comments]);
    }
}
