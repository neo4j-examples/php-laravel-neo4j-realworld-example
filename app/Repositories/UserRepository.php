<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Node;
use PharIo\Manifest\Author;
use RuntimeException;
use function array_filter;
use function array_merge;
use function compact;
use function sprintf;

class UserRepository
{
    public function __construct(private readonly Session $session)
    {
    }

    public function findByUsername(string $username): User
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (u:User {username: $username}) RETURN u
        CYPHER, ['username' => $username]);

        return $this->getUserFromResult($result, $username);
    }

    public function findByEmail(string $email): User
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $email}) RETURN u
        CYPHER, ['email' => $email]);

        return $this->getUserFromResult($result, $email);
    }

    public function update(?string $email, ?string $username, ?string $bio, ?string $image): User
    {
        $toUpdate = compact('email', 'username', 'bio', 'image');
        $user = array_merge((array) auth()->user()?->getAttribute('user'), array_filter($toUpdate, static fn ($x) => $x !== null));
        $result = $this->session->run(<<<'CYPHER'
        MATCH (u:User {email: $email})
        SET u.username = $username,
            u.bio = $bio,
            u.image = $image
        RETURN u
        CYPHER, $user);

        return $this->getUserFromResult($result, $user['username']);
    }

    public function create(string $email, string $username, string $password): User
    {
        $result = $this->session->run(<<<'CYPHER'
        CREATE (u:User {
          email: $email,
          username: $username,
          passwordHash: $passwordHash,
          bio: '',
          image: ''
        })
        RETURN u
        CYPHER, [
            'email' => $email,
            'username' => $username,
            'passwordHash' => Hash::make($password)
        ]);

        return $this->getUserFromResult($result, $username);
    }

    public function follow(string $usernameA, string $usernameB): User
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (self:User {username: $usernameA}), (u:User {username: $usernameB})
        MERGE (self) - [:FOLLOWS] -> (u)
        RETURN u
        CYPHER, compact('usernameA', 'usernameB'));

        return $this->getUserFromResult($result, $usernameB);
    }

    public function following(string $user, array $usernames): array
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (:User {username: $user}) - [:FOLLOWS] -> (user:User)
        WHERE user.username IN $usernames
        RETURN apoc.map.fromPairs(collect([user.username, true])) AS results
        CYPHER, compact('user', 'usernames'))
            ->getAsCypherMap(0)
            ->getAsCypherMap('results')
            ->toArray();
    }

    public function unfollow(string $usernameA, string $usernameB): User
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (:User {username: $usernameA}) - [f:FOLLOWS] -> (u:User {username: $usernameB})
        DELETE f
        RETURN u
        CYPHER, compact('usernameA', 'usernameB'));

        return $this->getUserFromResult($result, $usernameB);
    }

    public function getAuthorFromArticle(array $slugs): array
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (article:Article) <- [:AUTHORED] - (user:User)
        WHERE article.slug IN $slugs
        RETURN apoc.map.fromPairs(collect([article.slug, user])) AS results
        CYPHER, ['slugs' => $slugs])
            ->getAsCypherMap(0)
            ->getAsCypherMap('results')
            ->map($this->mapUser(...))
            ->toArray();
    }

    public function getCommentAuthors(array $ids): array
    {
        return $this->session->run(<<<'CYPHER'
        MATCH (c:Comment) <- [:AUTHORED] - (user:User)
        WHERE c.id IN $ids
        RETURN apoc.map.fromPairs(collect([c.id, user])) AS results
        CYPHER, ['ids' => $ids])
            ->getAsCypherMap(0)
            ->getAsCypherMap('results')
            ->map($this->mapUser(...))
            ->toArray();
    }

    private function getUserFromResult(SummarizedResult $result, string $username): User
    {
        if ($result->isEmpty()) {
            throw new RuntimeException(sprintf('Cannot find user with username: "%s"', $username));
        }

        return $this->mapUser($result->getAsCypherMap(0)->getAsNode('u'));
    }

    private function mapUser(Node $node): User
    {
        $user = $node->getProperties();

        return new User(
            username: $user['username'],
            email: $user['email'],
            bio: $user['bio'] ?? '',
            image: $user['image'] ?? '',
            passwordHash: $user['passwordHash']
        );
    }

}
