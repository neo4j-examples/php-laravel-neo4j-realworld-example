<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexArticleNodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('Article', static function (Blueprint $node) {
            $node->string('slug')->unique('article_slug_unique');
            $node->index('title', 'article_title_index');
            $node->fullText('description', 'article_description_fulltext');
            $node->fullText('body', 'article_body_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Article', static function (Blueprint $node) {
            $node->dropIndex('article_slug_unique');
            $node->dropIndex('article_title_index');
            $node->dropIndex('article_description_fulltext');
            $node->dropIndex('article_body_fulltext');
        });
    }
}
