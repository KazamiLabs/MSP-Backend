<?php
namespace App\Drivers\Bangumi;

use App\Drivers\Bangumi\Base;
use CURLFile;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Requests_Hooks;
use Requests_Session;

class Kisssub extends Base
{
    const HOST = 'http://www.kisssub.org';
    private $session;
    private $username;
    private $password;

    public function __construct(
        Requests_Session $session,
        string $username,
        string $password
    ) {
        // user id, user key 注入
        $this->username = $username;
        $this->password = $password;

        $session->url = self::HOST;

        $this->session = $session;
    }

    public function upload()
    {
        // 数据校验
        $validator = Validator::make($this->data, [
            'post_id'      => 'required|integer',
            'title'        => 'required|min:1',
            'author'       => 'required|min:1',
            'content'      => 'required|min:1',
            'torrent_name' => 'required|min:1',
            'torrent_path' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            Log::warning(
                '数据检验失败',
                $validator->errors()->all()
            );
            throw new Exception('Parameter error');
        }

        if (!is_file($this->data['torrent_path'])) {
            throw new Exception("Torrent [{$this->data['torrent_path']}] isn't validate file");
        }
        $torrent = new CURLFile($this->data['torrent_path'], 'application/octet-stream', $this->data['torrent_name']);

        $data = [
            'sort_id'     => '1',
            'title'       => $this->data['title'],
            'intro'       => $this->data['content'],
            'discuss_url' => '',
            'user_id'     => $this->username,
            'api_key'     => $this->password,
            'bt_file'     => $torrent,
        ];

        if ($this->data['author'] == 'Mabors-Raws') {
            $data['sort_id'] = '6';
        }

        $hook = new Requests_Hooks();
        $hook->register('curl.before_send', function ($ch) use ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        });
        $options = [
            'hooks' => $hook,
        ];

        try {
            $response = $this->session->post('/addon.php?r=api/post/76cad81b', null, null, $options);

            Log::info('Kisssub 上传响应', [$response]);
            $this->logInfo($response->body);

            $data = json_decode($response->body);

            Log::info("Kisssub 上传 ID {$data->info_hash}");
            $this->logInfo($data->info_hash);

            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '爱恋动漫',
                'site_id'    => $data->info_hash,
                'sync_state' => $data->status,
            ];
        } catch (Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '爱恋动漫',
                'site_id'    => 0,
                'sync_state' => 'failed',
            ];
            $this->logInfo($e->getMessage());
            $this->callback();

            Log::error("Kisssub 上传异常 {$e->getMessage()}", [$e]);

            throw $e;
        }
    }
}
