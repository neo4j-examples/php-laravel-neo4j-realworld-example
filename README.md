# ![RealWorld Example App](logo.png)

Laravel and Neo4j codebase containing real world examples (CRUD, auth, advanced patterns, etc) that adheres to the [RealWorld](https://github.com/gothinkster/realworld) spec and API.


[Demo](https://demo.realworld.io/)

[RealWorld](https://github.com/gothinkster/realworld)


This codebase was created to demonstrate a fully fledged fullstack application built with Laravel including CRUD operations, authentication, routing, pagination, and more.

We've gone to great lengths to adhere to the Laravel community styleguides & best practices.

For more information on how to this works with other frontends/backends, head over to the [RealWorld](https://github.com/gothinkster/realworld) repo.


# How it works

The API is built with laravel.

The API connects to Neo4J to handle persistent data.

The frontend can be freely chosen.

# Getting started

Requirements:

- PHP 8.3.36+, 8.4.27+ or 8.5.12+ with Composer 2. Earlier patch releases are vulnerable to CVE-2026-109618 and CVE-2026-109619, and `composer install` refuses to run on them.
- Neo4j 5 (the included `docker-compose.yml` starts one)

Copy .env.example to actual .env file
> cp .env.example .env

Change the Neo4j connection values if needed to connect to the correct database. Use a `bolt://` URL when connecting to a single Neo4j server.
> NEO4J_URL=bolt://localhost:7687
>
> NEO4J_USERNAME=neo4j
>
> NEO4J_PASSWORD=password

Start Neo4j:
> docker compose up -d

Install dependencies and generate the application key:
> composer install
>
> php artisan key:generate

Create the Neo4j constraints and indexes:
> docker compose exec -T neo4j cypher-shell -u neo4j -p password < database/neo4j/schema.cypher

Optionally load demo data. The users `alice@example.com`, `bob@example.com` and `carol@example.com` can log in with the password `password123`:
> docker compose exec -T neo4j cypher-shell -u neo4j -p password < database/neo4j/seed.cypher

Both scripts are plain Cypher and safe to run again. Without Docker, run them with `cypher-shell -a bolt://localhost:7687 -u neo4j -p password -f <file>` or paste them into Neo4j Browser. `php artisan migrate` and `php artisan db:seed` are not supported yet (see [TODO](TODO)).

Run app:
> php artisan serve

# Running the tests

The feature tests run against the Neo4j database configured in `.env`. They delete every node in that database, so do not point them at data you want to keep.
> vendor/bin/phpunit

The app also passes the official [RealWorld API spec](https://github.com/gothinkster/realworld/tree/main/specs/api) tests, which CI runs with [Hurl](https://hurl.dev). The suite sends more than 60 requests a minute, so set `API_RATE_LIMIT=1000` in `.env` before running it locally:
> git clone --depth 1 https://github.com/gothinkster/realworld.git /tmp/realworld
>
> HOST=http://localhost:8000 /tmp/realworld/specs/api/run-api-tests-hurl.sh
