<?php

namespace App\Drivers\Bangumi;

use App\Drivers\Bangumi\Base;
use Converter\HTMLConverter;
use CURLFile;
use Exception;
use HtmlParser\ParserDom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Requests_Cookie_Jar;
use Requests_Session;

class AcgRip extends Base
{
    const HOST            = 'https://acg.rip';
    const COOKIE_MAINNAME = 'AcgRip';
    const COOKIE_EXPIRE   = 315360000;

    private $default = [];
    private $session;
    private $username;
    private $password;

    public function __construct(
        Requests_Session $session,
        string $username,
        string $password
    ) {
        // 账户密码注入
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

        // 默认值预置
        $this->default = [
            'category_id' => 1,
        ];

        // 尝试登录
        $this->login();
    }

    public function login()
    {
        if ($this->isLogin()) {
            return;
        }

        Log::debug("ACG.RIP: {$this->username} need login");
        $this->logInfo("ACG.RIP: {$this->username} need login");

        $data = [
            'utf8'               => '✓',
            'authenticity_token' => $this->getToken('/users/sign_in'),
            'user[email]'        => $this->username,
            'user[password]'     => $this->password,
            'user[remember_me]'  => 1,
            'commit'             => '登录',
        ];

        $response = $this->session->post('/users/sign_in', null, $data);

        Log::debug("AcgRip 登录响应", [$response]);
        $this->logInfo($response->body);

    }

    public function checkAccount(): bool
    {
        return $this->isLogin();
    }

    public function upload()
    {
        // 数据校验
        if (!$this->validate([
            'post_id'      => 'required|integer',
            'title'        => 'required|min:1',
            'bangumi'      => 'required|min:1',
            'group'        => 'required',
            'content'      => 'required',
            'year'         => 'required|integer|min:2000',
            'torrent_name' => 'required|min:1',
            'torrent_path' => 'required|min:1',
        ])) {
            throw new Exception($this->validateErrors()->first());
        }

        if (!is_file($this->data['torrent_path'])) {
            throw new Exception("Torrent [{$this->data['torrent_path']}] isn't validate file");
        }
        $torrent = new CURLFile($this->data['torrent_path'], 'application/x-bittorrent', $this->data['torrent_name']);

        $coverter = new HTMLConverter($this->data['content']);

        $data = [
            'post[title]'        => $this->data['title'],
            'post[content]'      => $coverter->toBBCode(),
            'year'               => $this->data['year'],
            'post[torrent]'      => $torrent,
            'authenticity_token' => $this->getToken('/cp/posts/upload'),
            'post[category_id]'  => $this->default['category_id'],
            'post[series_id]'    => 0,
            'commit'             => '发布',
            'utf8'               => '✓',
            'post[post_as_team]' => 0,
        ];

        // $data['series_id'] = $this->getSeries($data['year'], $this->data['bangumi']);

        $teams = Collection::make([
            '幻之字幕组',
            '幻之字幕組',
            'Mabors-Sub',
        ]);

        if ($teams->contains($this->data['group'])) {
            $data['post[post_as_team]'] = 1;
        }

        $hook = new \Requests_Hooks();
        $hook->register('curl.before_send', function ($ch) use ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        });
        try {
            $response = $this->session->post('/cp/posts', ['Referer' => 'https://acg.rip/cp/posts/upload'], null, ['hooks' => $hook]);

            Log::debug('AcgRip 上传响应', [$response]);
            $this->logInfo($response->body);

            $siteId = $this->getSiteId($this->data['title']);

            Log::debug("AcgRip 上传 ID {$siteId}");
            $this->logInfo($siteId);

            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'ACG.RIP',
                'site_id'    => $siteId,
                'sync_state' => 'success',
            ];
        } catch (\Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'ACG.RIP',
                'site_id'    => 0,
                'sync_state' => 'failed',
            ];
            $this->logInfo($e->getMessage());
            $this->callback();
            $this->cacheCookie();

            Log::error("AcgRip 上传异常 {$e->getMessage()}", [$e]);

            throw $e;
        }
    }

    public function __destruct()
    {
        $this->cacheCookie();
    }

    protected function isLogin(): bool
    {
        $response = $this->session->get('/cp/posts', [], ['follow_redirects' => false]);

        Log::debug("Acgrip 登录检测: {$response->status_code}", [$response]);
        $this->logInfo("Acgrip 登录检测: {$response->status_code}");

        return $response->status_code === 200;
    }

    private function getToken(string $url): string
    {
        $response = $this->session->get($url);
        if (empty($response->body)) {
            throw new Exception("NON Response on {$url}");
        }
        $regx    = '/<input [^>]*?\sname="authenticity_token" value="(.*?)"/';
        $matches = [];
        if (preg_match($regx, $response->body, $matches)) {
            return $matches[1];
        } else {
            return '';
        }
    }

    private function getSeries(string $year, string $bangumiTitle)
    {
        $series_rsp   = $this->session->get("/ajax/get_series?year={$year}");
        $series_o_lst = json_decode($series_rsp->body, 1);
        $series_lst   = array_column($series_o_lst, 'name', 'id');
        $series_id    = 0;
        foreach ($series_lst as $key => $series) {
            if (mb_strstr($series, $bangumiTitle, false, 'utf-8')) {
                $series_id = $key;
                break;
            }
        }
        return $series_id;
    }

    private function dealResponse(string $body): ParserDom
    {
        return new ParserDom("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>{$body}");
    }

    private function getSiteId(string $title)
    {
        $response = $this->session->get("/cp/posts");
        $dom      = $this->dealResponse($response->body);
        $ls       = $dom->find("div.list-group-item-heading a");
        $siteId   = 0;
        foreach ($ls as $item) {
            $plainText = $item->getPlainText();
            if ($plainText == $title) {
                $href    = $item->getAttr('href');
                $matches = [];
                $flag    = preg_match('/\/t\/(\d+)/', $item->getAttr('href'), $matches);
                if ($flag && isset($matches[1])) {
                    $siteId = $matches[1];
                    break;
                }
            }
        }
        return $siteId;
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
