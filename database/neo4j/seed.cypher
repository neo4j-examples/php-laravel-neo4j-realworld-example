// Demo data. Every user can log in with the password "password123".
// Run schema.cypher first. Re-running this script does not duplicate data.
// TODO: move this into Laravel seeders once neo4j-laravel supports `php artisan db:seed`.

UNWIND [
  {username: 'alice', email: 'alice@example.com', bio: 'Graph enthusiast.', image: null},
  {username: 'bob', email: 'bob@example.com', bio: 'Writes about Laravel.', image: null},
  {username: 'carol', email: 'carol@example.com', bio: null, image: null}
] AS row
MERGE (u:User {username: row.username})
ON CREATE SET
  u.email = row.email,
  u.bio = row.bio,
  u.image = row.image,
  u.passwordHash = '$2y$10$NQ4FTztib2iniC/T/N/pWefw0DyjzQey4P7w48YVivkjolYTW352S',
  u.created_at = '2026-01-01 09:00:00',
  u.updated_at = '2026-01-01 09:00:00';

UNWIND [
  {author: 'alice', slug: 'why-graphs', title: 'Why graphs', description: 'Relationships as first-class citizens', body: 'Most data is connected. A graph database stores those connections directly.', tags: ['neo4j', 'graphs'], createdAt: '2026-01-02T10:00:00.000+00:00'},
  {author: 'alice', slug: 'modelling-a-blog-in-cypher', title: 'Modelling a blog in Cypher', description: 'Users, articles, comments and tags', body: 'Each concept becomes a node label and each interaction a relationship type.', tags: ['neo4j', 'cypher'], createdAt: '2026-01-03T10:00:00.000+00:00'},
  {author: 'bob', slug: 'laravel-and-neo4j', title: 'Laravel and Neo4j', description: 'Eloquent models backed by nodes', body: 'neo4j-laravel lets Eloquent models read and write Neo4j nodes.', tags: ['laravel', 'neo4j'], createdAt: '2026-01-04T10:00:00.000+00:00'},
  {author: 'carol', slug: 'hello-world', title: 'Hello world', description: 'My first article', body: 'Hello from the RealWorld example app.', tags: [], createdAt: '2026-01-05T10:00:00.000+00:00'}
] AS row
MATCH (author:User {username: row.author})
MERGE (a:Article {slug: row.slug})
ON CREATE SET
  a.id = randomUUID(),
  a.title = row.title,
  a.description = row.description,
  a.body = row.body,
  a.createdAt = row.createdAt,
  a.updatedAt = row.createdAt
MERGE (author)-[:AUTHORED]->(a)
FOREACH (name IN row.tags |
  MERGE (t:Tag {name: name})
  ON CREATE SET t.id = randomUUID(), t.created_at = '2026-01-01 09:00:00', t.updated_at = '2026-01-01 09:00:00'
  MERGE (t)-[:TAGGED]->(a)
);

UNWIND [
  {id: 1, article: 'why-graphs', author: 'bob', body: 'Great introduction!', createdAt: '2026-01-02T12:00:00.000+00:00'},
  {id: 2, article: 'why-graphs', author: 'carol', body: 'Thanks, this helped me get started.', createdAt: '2026-01-02T13:00:00.000+00:00'},
  {id: 3, article: 'laravel-and-neo4j', author: 'alice', body: 'Nice to see Eloquent on a graph.', createdAt: '2026-01-04T12:00:00.000+00:00'}
] AS row
MATCH (a:Article {slug: row.article})
MATCH (author:User {username: row.author})
MERGE (c:Comment {id: row.id})
ON CREATE SET c.body = row.body, c.createdAt = row.createdAt, c.updatedAt = row.createdAt
MERGE (a)-[:HAS_COMMENT]->(c)
MERGE (author)-[:AUTHORED]->(c);

MERGE (s:Sequence {name: 'Comment'})
ON CREATE SET s.value = 0
WITH s
MATCH (c:Comment)
WITH s, max(c.id) AS highest
SET s.value = CASE WHEN s.value < highest THEN highest ELSE s.value END;

UNWIND [
  {follower: 'bob', followed: 'alice'},
  {follower: 'carol', followed: 'alice'},
  {follower: 'alice', followed: 'bob'}
] AS row
MATCH (follower:User {username: row.follower})
MATCH (followed:User {username: row.followed})
MERGE (follower)-[:FOLLOWING]->(followed);

UNWIND [
  {user: 'bob', article: 'why-graphs'},
  {user: 'carol', article: 'why-graphs'},
  {user: 'alice', article: 'laravel-and-neo4j'}
] AS row
MATCH (u:User {username: row.user})
MATCH (a:Article {slug: row.article})
MERGE (u)-[:FAVORITED]->(a);
