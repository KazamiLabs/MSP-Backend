<?php

namespace App\Drivers\Bangumi;

use App\Drivers\Bangumi\Base;
use App\Tools\Ocr\JdWanXiang\JdWanXiang;
use CURLFile;
use Exception;
use HtmlParser\ParserDom;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Requests_Cookie_Jar;
use Requests_Hooks;
use Requests_Session;

class Dmhy extends Base
{
    const HOST             = 'https://share.dmhy.org';
    const COOKIE_MAINNAME  = 'Dmhy';
    const COOKIE_EXPIRE    = 315360000;
    const MAX_LOGIN_FAILED = 3;

    private $vcode = 'bangumi/dmhy/vcode.png';
    private $session;
    private $ocr;
    private $username;
    private $password;
    private $loginFailedCount = 0;

    public function __construct(
        Requests_Session $session,
        JdWanXiang $ocr,
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

        // OCR API 验证码识别注入
        $this->ocr = $ocr;

        // 尝试登录
        $this->login();
    }

    public function login()
    {
        if ($this->isLogin() === true) {
            return;
        }

        Log::info("Dmhy: {$this->username} need login");
        $this->logInfo("Dmhy: {$this->username} need login");

        $response = $this->session->get('/user/login?goto=%2Ftopics%2Fadd');
        // 验证码识别
        $vcodeUrl  = $this->getVcodeUrl($response->body);
        $vcode_ocr = $this->ocr($vcodeUrl);

        Log::info("Dmhy validate code: {$vcode_ocr}");
        $this->logInfo("Dmhy 识别的验证码: {$vcode_ocr}");

        $params = [
            'goto'         => '/topics/add',
            'email'        => $this->username,
            'password'     => $this->password,
            'login_node'   => 0,
            'cookietime'   => self::COOKIE_EXPIRE,
            'captcha_code' => strtolower($vcode_ocr),
        ];

        $response = $this->session->post('/user/login', null, $params);
        // $this->logInfo($response->body);

        $message = $this->getMessage($response->body);
        if (!Str::contains($message, '登入成功')) {
            // 计数
            ++$this->loginFailedCount;

            Log::info("Dmhy login failed: {$message}");
            $this->logInfo($response->body);

            if ($this->loginFailedCount >= self::MAX_LOGIN_FAILED) {
                throw new Exception('Dmhy Login failed with ' . self::MAX_LOGIN_FAILED . 'try(ies).');
            } else {
                return $this->login();
            }
        } else {
            return;
        }
    }
    public function upload()
    {
        if (!$this->validate([
            'post_id'      => 'required',
            'title'        => 'required',
            'bangumi'      => 'required',
            'group'        => 'required',
            'content'      => 'required',
            'torrent_name' => 'required',
            'torrent_path' => 'required',
        ])) {
            throw new Exception($this->validateErrors()->first());
        }

        if (!is_file($this->data['torrent_path'])) {
            throw new Exception("Torrent [{$this->data['torrent_path']}] isn't validate file");
        }

        $torrent = new CURLFile(
            $this->data['torrent_path'],
            'application/x-bittorrent',
            $this->data['torrent_name']
        );

        $teams = Collection::make([
            ['id' => 0, 'name' => '个人发布'],
            ['id' => 430, 'name' => '幻之字幕组'],
            ['id' => 430, 'name' => '幻之字幕組'],
            ['id' => 430, 'name' => 'Mabors-Sub'],
            ['id' => 726, 'name' => 'Mabors-Raws'],
        ]);

        $data = [
            'sort_id'       => 2, // 分类ID，动画
            'bt_data_title' => $this->data['title'],
            'bt_data_intro' => $this->data['content'],
            'bt_file'       => $torrent,
            'team_id'       => $teams->where('name', $this->data['group'])->min('id'),
        ];

        $hook = new Requests_Hooks();
        $hook->register('curl.before_send', function ($ch) use ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        });
        try {
            $response = $this->session->post('/topics/add', [], null, ['hooks' => $hook]);

            Log::info('Dmhy 上传响应', [$response]);
            $this->logInfo('Dmhy 上传响应');
            $this->logInfo($response->body);

            $message = $this->getMessage($response->body);
            if (Str::contains($message, '成功') === false) {
                throw new Exception("Upload failed: {$message}");
            }
            $siteId = $this->getSiteId($this->data['title']);

            Log::info("Dmhy 上传 ID {$siteId}");
            $this->logInfo($siteId);

            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '动漫花园',
                'site_id'    => $siteId,
                'sync_state' => 'success',
            ];
        } catch (Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '动漫花园',
                'site_id'    => 0,
                'sync_state' => 'failed',
            ];
            $this->logInfo($e->getMessage());
            $this->callback();
            $this->cacheCookie();

            Log::error("Dmhy 上传异常 {$e->getMessage()}", [$e]);

            throw $e;
        }

    }

    public function __destruct()
    {
        $this->cacheCookie();
    }

    private function isLogin(): bool
    {
        $response = $this->session->get('/user', [], ['follow_redirects' => false]);

        Log::info("Dmhy 登录检测: {$response->status_code}", [$response]);
        $this->logInfo("Dmhy 登录检测: {$response->status_code}");

        return $response->status_code === 200;
    }

    private function getVcodeUrl(string $body): string
    {
        $regx    = '/<img id="captcha_img".*? src="(.+?)"/';
        $matches = [];
        if (preg_match($regx, $body, $matches)) {
            return $matches[1];
        } else {
            return '';
        }
    }

    private function ocr(string $url): string
    {
        $response = $this->session->get($url);
        Storage::put($this->vcode, $response->body);

        $result = '';
        try {
            $result = $this->ocr->forImageFile(Storage::path($this->vcode));
        } catch (Exception $e) {
            // OCR 识别异常记录到日志
            Log::error("OCR 识别异常: {$e->getMessage()}", [$e]);
            $this->logInfo("OCR 识别异常: {$e->getMessage()}");
        }
        return $result;
    }

    private function getMessage(string $body): string
    {
        $regx    = '/<li class="text_bold text_blue">(.*?)</i';
        $matches = [];
        if (\preg_match($regx, $body, $matches)) {
            return $matches[1];
        } else {
            return '';
        }
    }

    private function getSiteId(string $title): string
    {
        $response = $this->session->get('/topics/mlist/scope/team');
        $dom      = $this->dealResponse($response->body);
        $alinks   = $dom->find('table#topic_list>tbody>tr>td.title>a');
        $siteId   = 0;
        foreach ($alinks as $alink) {
            $plainText = $alink->getPlainText();
            if ($plainText == $title) {
                $href    = $alink->getAttr('href');
                $matches = [];
                $flag    = preg_match('/(?<=\/)(\d+)(?=_)/', $href, $matches);
                if ($flag && isset($matches[1])) {
                    $siteId = $matches[1];
                    break;
                }
            }
        }
        return $siteId;
    }

    private function dealResponse(string $body): ParserDom
    {
        return new ParserDom("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>{$body}");
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
