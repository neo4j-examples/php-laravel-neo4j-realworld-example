<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexTagNodes extends Migration
{
    public function up(): void
    {
        Schema::table('Tag', static function (Blueprint $node) {
            $node->string('name')->unique('tag_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('Tag', static function (Blueprint $node) {
            $node->dropIndex('tag_name_unique');
        });
    }
}
