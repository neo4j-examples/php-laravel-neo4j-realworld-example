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

Copy .env.example to actual .env file
> cp .env.example .env

Change the bolt_dsn value if needed to connect to the correct database
> BOLT_DSN=bolt://neo4j:test@localhost

Install dependencies:
> composer install

Run app:
> php artisan serve
