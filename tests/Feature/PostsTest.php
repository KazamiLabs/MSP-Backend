<?php

namespace Tests\Feature;

use App\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostsTest extends TestCase
{
    // use RefreshDatabase;
    /**
     * Test fetch post list
     */
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

    /**
     * Test fetch single post
     */
    public function testGetFirstPost()
    {
        $postId   = 1;
        $response = $this->json('GET', "/api/post/{$postId}/admin");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $postId,
            ])
            ->assertJsonStructure([
                'id', 'post_author', 'post_content', 'post_title',
                'post_excerpt', 'post_status', 'comment_status', 'ping_status',
                'post_name', 'to_ping', 'pinged', 'post_date', 'post_date_gmt',
                'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent',
                'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count', 'created_at',
                'updated_at',
                'author' => [
                    'id', 'user_login', 'user_nicename', 'user_email',
                    'user_url', 'user_registered', 'user_activation_key',
                    'user_status', 'display_name', 'created_at', 'updated_at',
                ],
            ]);
    }

    /**
     * Test add a new post
     */
    public function testCommitAPost()
    {
        $post     = factory(Post::class)->make();
        $data     = $post->toArray();
        $response = $this->json('POST', '/api/post/admin', $data);
        $response->assertStatus(200);
        $this->assertTrue(true);
    }

    /**
     * Test modify an exist post
     */
    public function testUpdateAPost()
    {
        $postId = 2;
        // 通过在 make 方法中嵌入数据以达到增加/修改工厂出来的假模型
        // 当中某项的值，估计是使用 array_merge() 或类似方法实现
        $post     = factory(Post::class)->make(['id' => $postId]);
        $response = $this->json('POST', "/api/post/{$postId}/admin", $data);
        $response->assertStatus(200);
        $this->assertTrue(true);
    }
}
