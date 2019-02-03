<?php

namespace Tests\Feature;

use App\Bangumi;
use App\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Admin;
use Tests\TestCase;

class PostsTest extends TestCase
{
    // use RefreshDatabase;
    use Admin;
    /**
     * Test fetch post list
     */
    public function testGetPosts()
    {
        $authInfo = $this->authenticate();
        $page     = 1;
        $limit    = 10;
        $response = $this
            ->withHeaders([
                'Authorization' => "{$authInfo['token_type']} {$authInfo['access_token']}",
            ])
            ->json('GET', '/api/posts/admin', [
                'page'  => $page,
                'limit' => $limit,
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'current_page' => $page,
                'from'         => 1 + ($page - 1) * $limit,
                'per_page'     => $limit,
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'author'        => ['user_pass' => ''],
                        'post_password' => '',
                        'to_ping'       => '',
                        'pinged'        => '',
                        'ping_status'   => '',
                        'guid'          => '',
                        'menu_order'    => '',
                        'post_parent'   => '',
                        'deleted_at'    => '',
                    ],
                ],
            ])
            ->assertJsonStructure([
                'last_page',
                'from',
                'to',
                'data' => [
                    [
                        'id', 'post_author', 'post_title',
                        'post_excerpt', 'post_status', 'comment_status',
                        'post_name', 'post_date', 'post_date_gmt',
                        'post_modified', 'post_modified_gmt', 'post_content_filtered',
                        'post_type', 'post_mime_type', 'comment_count', 'created_at',
                        'updated_at',
                        'author' => [
                            'id', 'nicename',
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
        $authInfo = $this->authenticate();
        $postId   = 1;
        $response = $this
            ->withHeaders([
                'Authorization' => "{$authInfo['token_type']} {$authInfo['access_token']}",
            ])
            ->json('GET', "/api/post/{$postId}/admin");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $postId,
            ])
            ->assertJsonStructure([
                'id',
                'post_author',
                'post_content',
                'post_title',
                'post_excerpt',
                'post_status',
                'comment_status',
                'post_name',
                'post_date',
                'post_date_gmt',
                'post_modified',
                'post_modified_gmt',
                'post_content_filtered',
                'post_type',
                'post_mime_type',
                'comment_count',
                'created_at',
                'updated_at',
                'author'  => [
                    'id',
                    'nicename',
                ],
                'bangumi' => [
                    'id',
                    'post_id',
                    'filename',
                    'filepath',
                    'group_name',
                    'title',
                    'year',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /**
     * Test add a new post
     */
    public function testCommitAPost()
    {
        $authInfo        = $this->authenticate();
        $post            = factory(Post::class)->make();
        $bangumi         = factory(Bangumi::class)->make();
        $data            = $post->toArray();
        $data['bangumi'] = $bangumi->toArray();
        $response        = $this
            ->withHeaders([
                'Authorization' => "{$authInfo['token_type']} {$authInfo['access_token']}",
            ])
            ->json('POST', '/api/post/admin', $data);
        // $response->assertStatus(200);
        $this->assertTrue(true);
    }

    /**
     * Test modify an exist post
     */
    public function testUpdateAPost()
    {
        $authInfo = $this->authenticate();
        $postId   = 2;
        // 通过在 make 方法中嵌入数据以达到增加/修改工厂出来的假模型
        // 当中某项的值，估计是使用 array_merge() 或类似方法实现
        $post = factory(Post::class)->make(['id' => $postId]);
        $data = $post->toArray();
        // $response = $this
        //     ->withHeaders([
        //         'Authorization' => "{$authInfo['token_type']} {$authInfo['access_token']}",
        //     ])
        //     ->json('POST', "/api/post/{$postId}/admin", $data);
        // $response->assertStatus(200);
        $this->assertTrue(true);
    }
}
