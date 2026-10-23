// Constraints and indexes used by the application. Every statement is idempotent.
// TODO: move this into Laravel migrations once neo4j-laravel supports `php artisan migrate`.

CREATE CONSTRAINT user_email_unique IF NOT EXISTS FOR (n:User) REQUIRE n.email IS UNIQUE;
CREATE CONSTRAINT user_username_unique IF NOT EXISTS FOR (n:User) REQUIRE n.username IS UNIQUE;
CREATE FULLTEXT INDEX user_bio_fulltext IF NOT EXISTS FOR (n:User) ON EACH [n.bio];
CREATE INDEX user_image_index IF NOT EXISTS FOR (n:User) ON (n.image);

CREATE CONSTRAINT article_slug_unique IF NOT EXISTS FOR (n:Article) REQUIRE n.slug IS UNIQUE;
CREATE INDEX article_title_index IF NOT EXISTS FOR (n:Article) ON (n.title);
CREATE INDEX article_created_at_index IF NOT EXISTS FOR (n:Article) ON (n.createdAt);
CREATE FULLTEXT INDEX article_description_fulltext IF NOT EXISTS FOR (n:Article) ON EACH [n.description];
CREATE FULLTEXT INDEX article_body_fulltext IF NOT EXISTS FOR (n:Article) ON EACH [n.body];

CREATE CONSTRAINT comment_id_unique IF NOT EXISTS FOR (n:Comment) REQUIRE n.id IS UNIQUE;

CREATE CONSTRAINT tag_name_unique IF NOT EXISTS FOR (n:Tag) REQUIRE n.name IS UNIQUE;

CREATE CONSTRAINT sequence_name_unique IF NOT EXISTS FOR (n:Sequence) REQUIRE n.name IS UNIQUE;
