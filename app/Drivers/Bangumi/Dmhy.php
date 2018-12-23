<?php

namespace App\Drivers\Bangumi;

use HtmlParser\ParserDom;
use App\Tools\Ocr\Ruokuai;
use Illuminate\Support\Facades\Config;

class Dmhy extends Base
{
    protected $host = 'http://share.dmhy.org';
    private $vcode  = '/tmp/dmhy-vcode.png';

    public function init()
    {
        $vcodeDir = storage_path('app/bangumi/dmhy');
        if (!is_dir($vcodeDir)) {
            re_mkdir($vcodeDir);
        }
        $this->vcode = "{$vcodeDir}/vcode.png";
    }
    public function login()
    {
        if ($this->isLogin() === true) {
            return;
        }
        echo "Dmhy: {$this->username} need login", PHP_EOL;
        $response  = $this->getSession()->get('/user/login?goto=%2Ftopics%2Fadd');
        $vcodeUrl  = $this->getVcodeUrl($response->body);
        $vcode_ocr = $this->ocr($vcodeUrl);
        $params    = [
            'goto'         => '/topics/add',
            'email'        => $this->username,
            'password'     => $this->password,
            'login_node'   => 0,
            'cookietime'   => 315360000,
            'captcha_code' => strtolower($vcode_ocr),
        ];
        echo "Dmhy validate code: {$vcode_ocr}", PHP_EOL;
        $response = $this->getSession()->post('/user/login', null, $params);
        // echo $ressponse->body, PHP_EOL;
        $message = $this->getMessage($response->body);
        if (mb_strpos($message, '登入成功') === false) {
            echo "Dmhy login failed: {$message}", PHP_EOL;
            return $this->login();
        } else {
            return;
        }
    }
    public function upload()
    {
        $require = ['post_id', 'title', 'bangumi', 'author', 'content', 'torrent_name', 'torrent_path'];
        foreach ($require as $field) {
            if (isset($this->data[$field])) {
                continue;
            }
            if (isset($this->default[$field])) {
                $this->data[$field] = $this->default[$field];
                continue;
            }
            throw new \Exception("Field {$field} is required");
        }

        if (!is_file($this->data['torrent_path'])) {
            throw new \Exception("Torrent [{$this->data['torrent_path']}] isn't validate file");
        }

        $torrent = new \CURLFile($this->data['torrent_path'], 'application/x-bittorrent', $this->data['torrent_name']);

        if ($this->data['author'] == '幻之字幕組') {
            $this->data['author'] = '幻之字幕组';
        }
        $teams = [
            '0'   => '个人发布',
            '430' => '幻之字幕组',
            '726' => 'Mabors-Raws',
        ];

        $data = [
            'sort_id'       => 2, // 分类ID，动画
            'bt_data_title' => $this->data['title'],
            'bt_data_intro' => $this->data['content'],
            'bt_file'       => $torrent,
            'team_id'       => array_search($this->data['author'], $teams),
        ];
        if ($data['team_id'] === false) {
            $data['team_id'] = 0;
        }
        $logfile = "{$this->logdir}/dmhy-{$this->data['post_id']}.log";

        $hook = new \Requests_Hooks();
        $hook->register('curl.before_send', function ($ch) use ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        });
        try {
            $response = $this->getSession()->post('/topics/add', [], null, ['hooks' => $hook]);
            file_put_contents($logfile, $response->body . PHP_EOL, FILE_APPEND);
            $message = $this->getMessage($response->body);
            if (mb_strpos($message, '成功') === false) {
                throw new \Exception("Upload failed: {$message}");
            }
            $siteId = $this->getSiteId($this->data['title']);
            file_put_contents($logfile, $siteId . PHP_EOL, FILE_APPEND);
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '动漫花园',
                'site_id'    => $siteId,
                'log_file'   => $logfile,
                'sync_state' => 'success',
            ];
        } catch (\Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '动漫花园',
                'site_id'    => 0,
                'log_file'   => $logfile,
                'sync_state' => 'failed',
            ];
            file_put_contents($logfile, $e->getMessage() . PHP_EOL, FILE_APPEND);
        }

    }

    protected function isLogin(): bool
    {
        $response = $this->getSession()->get('/topics/add');
        $dom      = $this->dealResponse($response->body);
        $ls       = $dom->find("a[href=/user/logout]");
        return is_array($ls) && count($ls) > 0;
    }

    private function getVcodeUrl(string $body)
    {
        $regx    = '/<img id="captcha_img".*? src="(.+?)"/';
        $matches = [];
        if (preg_match($regx, $body, $matches)) {
            return $matches[1];
        } else {
            return '';
        }
    }

    private function ocr(string $url)
    {
        $vcode_filepath = $this->vcode;
        $response       = $this->getSession()->get($url);
        file_put_contents($vcode_filepath, $response->body);
        $config = Config::get('ruokuai');
        if (empty($config['username']) || empty($config['password'])) {
            throw new \Exception('Failed to load ruokuai config');
        }
        $ocr    = new Ruokuai($config['username'], $config['password']);
        $result = $ocr->forImageFile($vcode_filepath);
        return $result;
    }

    private function getMessage(string $body)
    {
        $regx    = '/<li class="text_bold text_blue">(.*?)</i';
        $matches = [];
        if (\preg_match($regx, $body, $matches)) {
            return $matches[1];
        } else {
            return '';
        }
    }

    private function getSiteId(string $title)
    {
        $response = $this->getSession()->get('/topics/mlist/scope/team');
        $dom      = $this->dealResponse($response->body);
        $alinks   = $dom->find('table#topic_list>tbody>tr>td.title>a');
        $siteId   = 0;
        foreach ($alinks as $alink) {
            $plainText = $alink->getPlainText();
            if ($plainText == $title) {
                $href    = $alink->getAttr('href');
                $matches = [];
                $flag    = preg_match('/(?<=\/)(\d+)(?=_)/', $alink->getAttr('href'), $matches);
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
}
