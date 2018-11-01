<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostsTest extends TestCase
{
    // use RefreshDatabase;
    public function testGetPosts()
    {
        $page     = 1;
        $limit    = 10;
        $response = $this->json('GET', '/api/posts/admin', [
            'page'  => $page,
            'limit' => $limit,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'current_page' => $page,
                'from'         => 1 + ($page - 1) * $limit,
                'to'           => $page * $limit,
                'per_page'     => $limit,
            ])
        // ->assertJsonMissing([
        //     'data' => [
        //         ['author' => ['user_pass' => '']],
        //     ],
        // ])
            ->assertJsonStructure([
                'last_page',
                'data' => [
                    ['id', 'post_author', 'post_content', 'post_title',
                        'post_excerpt', 'post_status', 'comment_status', 'ping_status',
                        'post_name', 'to_ping', 'pinged', 'post_date', 'post_date_gmt',
                        'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent',
                        'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count', 'created_at',
                        'updated_at', 'access_api',
                        'author' => [
                            'id', 'user_login', 'user_nicename', 'user_email',
                            'user_url', 'user_registered', 'user_activation_key',
                            'user_status', 'display_name', 'created_at', 'updated_at',
                        ],
                    ],
                ],
            ]);
    }
}
