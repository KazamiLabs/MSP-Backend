<?php
namespace App\Drivers\Bangumi;

use App\Drivers\Bangumi\Base;
use CURLFile;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Requests_Cookie_Jar;
use Requests_Session;

class MoeBangumi extends Base
{
    const HOST            = 'https://bangumi.moe';
    const COOKIE_MAINNAME = 'MoeBangumi';
    const COOKIE_EXPIRE   = 315360000;

    private $myteam      = [];
    private $deal_myteam = [];
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

        // cookie 处理
        $cookieJar = Cache::get('bangumi-sync:' . self::COOKIE_MAINNAME . ":{$this->username}");
        if ($cookieJar instanceof Requests_Cookie_Jar) {
            unset($session->options['cookies']);
            $session->options['cookies'] = $cookieJar;
        }

        $this->session = $session;

        // 尝试登录
        $this->login();
    }

    public function upload()
    {
        // 数据校验
        $validator = Validator::make($this->data, [
            'post_id'      => 'required|integer',
            'title'        => 'required|min:1',
            'bangumi'      => 'required|min:1',
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

        // 获取团队数据
        $this->getTeamData();
        if ($this->data['author'] == '幻之字幕組') {
            $this->data['author'] = '幻之字幕组';
        }
        if (isset($this->deal_myteam[$this->data['author']])) {
            $team_id = $this->deal_myteam[$this->data['author']];
        } else {
            $team_id = '';
        }
        $torrent_form = [
            'file'    => $torrent,
            'team_id' => $team_id,
        ];

        // 尝试上传种子文件
        $hook = new \Requests_Hooks();
        $hook->register('curl.before_send', function ($ch) use ($torrent_form) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $torrent_form);
        });
        $response = $this->session->post('/api/v2/torrent/upload', null, null, ['hooks' => $hook]);

        Log::info('Moe Bangumi 种子上传响应', [$response]);
        $this->logInfo($response->body);

        $torrent_res = json_decode($response->body);
        if ($torrent_res->success === false) {
            throw new Exception("Moe Bangumi: Failed to upload torrent.");
        }

        $data = [
            'category_tag_id' => '549ef250fe682f7549f1ea91',
            'title'           => $this->data['title'],
            'introduction'    => $this->data['content'],
            'tag_ids'         => [],
            'team_id'         => $team_id,
            'file_id'         => $torrent_res->file_id,
        ];

        // Wordpress特殊字符处理
        $data['introduction'] = str_replace([
            '<fieldset>', '</fieldset>',
            '<legend>引用</legend>',
        ], ['', '', '<br /><br />引用<br />'], $data['introduction']);

        $header = [
            'Content-Type' => 'application/json',
        ];
        $data_raw = json_encode($data, JSON_UNESCAPED_UNICODE);
        try {
            $response = $this->session->post('/api/torrent/add', $header, $data_raw);

            Log::info('Moe Bangumi 发布提交响应', [$response]);
            $this->logInfo($response->body);

            $data           = json_decode($response->body);
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '萌番组',
                'site_id'    => $data->torrent->_id,
                'sync_state' => 'success',
            ];
        } catch (Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '萌番组',
                'site_id'    => 0,
                'sync_state' => 'failed',
            ];
            $this->logInfo($e->getMessage());
            $this->callback();
            $this->cacheCookie();

            Log::error("Moe Bangumi 上传异常 {$e->getMessage()}", [$e]);

            throw $e;
        }
    }

    public function __destruct()
    {
        $this->cacheCookie();
    }

    private function login()
    {
        if ($this->isLogin()) {
            return;
        }

        Log::info("Moe Bangumi: {$this->username} need login");
        $this->logInfo("Moe Bangumi: {$this->username} need login");

        $data = [
            'username' => $this->username,
            'password' => md5($this->password),
        ];

        $header = [
            'Content-Type' => 'application/json',
        ];

        $response = $this->session->post(
            '/api/user/signin',
            $header,
            json_encode($data, JSON_UNESCAPED_UNICODE)
        );

        Log::info("Moe Bangumi 登录响应", [$response]);
        $this->logInfo($response->body);

        $login_result = json_decode($response->body, true);
        if ($login_result['success'] === false) {
            throw new Exception("Login failed! {$response->body}");
        }

    }

    private function isLogin(): bool
    {
        $response = $this->session->get('/api/user/session');

        Log::info("Moe Bangumi 登录检测", [$response]);
        $this->logInfo("Moe Bangumi 登录检测");

        $data = json_decode($response->body);
        return isset($data->username) && $data->username === $this->username ||
        isset($data->email) && $data->email === $this->username;
    }

    private function getTeamData()
    {
        $team_resp = $this->session->get('/api/team/myteam');
        $team      = json_decode($team_resp->body, true);
        if (is_array($team) && count($team) > 0) {
            $this->myteam      = $team;
            $this->deal_myteam = array_column($this->myteam, '_id', 'name');
        }
    }

    private function cacheCookie()
    {
        // Cookie 缓存
        Cache::set(
            'bangumi-sync:' . self::COOKIE_MAINNAME . ":{$this->username}",
            $this->session->options['cookies'],
            self::COOKIE_EXPIRE
        );
    }
}
