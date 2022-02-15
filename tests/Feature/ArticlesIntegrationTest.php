<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ArticlesIntegrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCreateArticle(): void
    {
        $response = $this->postJson('/api/articles', [
            'article' => [
                'title' => 'Test article',
                'description' => 'Simple description',
                'body' => 'This is a short blogpost about testing',
                'tagList' => [
                    'test',
                    'ignore'
                ]
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            "article" => [
//                "slug" => "string",
                "title" => "Test article",
                "description" => "Simple description",
                "body" => "This is a short blogpost about testing",
                "tagList" => [
                    "test",
                    "ignore"
                ],
//                "createdAt" => "2022-02-02T16:32:41.898Z",
//                "updatedAt" => "2022-02-02T16:32:41.899Z",
//                "favorited" => true,
//                "favoritesCount" => 0,
//                "author" => [
//                  "username" => "string",
//                  "bio" => "string",
//                  "image" => "string",
//                  "following" => true
//                ],
              ]
            ]
        );
    }
}
