<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('neo4j:indexes', function () {
    $statements = [
        'CREATE CONSTRAINT user_email_unique IF NOT EXISTS FOR (n:User) REQUIRE n.email IS UNIQUE',
        'CREATE CONSTRAINT user_username_unique IF NOT EXISTS FOR (n:User) REQUIRE n.username IS UNIQUE',
        'CREATE FULLTEXT INDEX user_bio_fulltext IF NOT EXISTS FOR (n:User) ON EACH [n.bio]',
        'CREATE INDEX user_image_index IF NOT EXISTS FOR (n:User) ON (n.image)',
        'CREATE CONSTRAINT article_slug_unique IF NOT EXISTS FOR (n:Article) REQUIRE n.slug IS UNIQUE',
        'CREATE INDEX article_title_index IF NOT EXISTS FOR (n:Article) ON (n.title)',
        'CREATE FULLTEXT INDEX article_description_fulltext IF NOT EXISTS FOR (n:Article) ON EACH [n.description]',
        'CREATE FULLTEXT INDEX article_body_fulltext IF NOT EXISTS FOR (n:Article) ON EACH [n.body]',
        'CREATE CONSTRAINT tag_name_unique IF NOT EXISTS FOR (n:Tag) REQUIRE n.name IS UNIQUE',
    ];

    foreach ($statements as $statement) {
        DB::connection('neo4j')->statement($statement);
    }

    $this->info('Neo4j constraints and indexes are in place.');
})->purpose('Create the Neo4j constraints and indexes used by the application');
