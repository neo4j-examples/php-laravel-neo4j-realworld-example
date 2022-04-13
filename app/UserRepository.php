<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\Databags\SummarizedResult;
use RuntimeException;
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

    public function update(string $email, string $username, string $bio, string $image): User
    {
        $user = array_merge((array) $authenticatable->getAttribute('user'), $requestedUser);
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

    public function following(string $usernameA, string $usernameB): bool
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (:User {username: $usernameA}) - [:FOLLOWS] -> (:User {username: $usernameB})
        RETURN true
        CYPHER, compact('usernameA', 'usernameB'));

        return !$result->isEmpty();
    }

    private function getUserFromResult(SummarizedResult $result, string $username): User
    {
        if ($result->isEmpty()) {
            throw new RuntimeException(sprintf('Cannot find user with username: "%s"', $username));
        }

        $user = $result->getAsCypherMap(0)
            ->getAsNode('u')
            ->getProperties();

        return new User(
            username: $user['username'],
            email: $user['email'],
            bio: $user['bio'] ?? '',
            image: $user['image'] ?? '',
            passwordHash: $user['passwordHash']
        );
    }

    public function unfollow(string $usernameA, string $usernameB): User
    {
        $result = $this->session->run(<<<'CYPHER'
        MATCH (u:User {username: $usernameB}) <- [f:FOLLOWS] - (self:User {email: $usernameA})
        DELETE f
        RETURN u
        CYPHER, compact('usernameA', 'usernameB'));

        return $this->getUserFromResult($result, $usernameB);
    }
}
