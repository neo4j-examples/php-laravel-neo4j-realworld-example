<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexUserNodes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('User', static function (Blueprint $node) {
            $node->string('email')->unique('user_email_unique');
            $node->string('username')->unique('user_username_unique');
            $node->fullText('bio', 'user_bio_fulltext');
            $node->index('image', 'user_image_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('User', static function (Blueprint $node) {
            $node->dropIndex('user_email_unique');
            $node->dropIndex('user_username_unique');
            $node->dropIndex('user_bio_fulltext');
            $node->dropIndex('user_image_index');
        });
    }
}
