<?php

namespace App\Drivers\Bangumi;

use Converter\HTMLConverter;
use HtmlParser\ParserDom;

class AcgRip extends Base
{
    protected $host   = 'https://acg.rip';
    private $year_lst = [];
    private $default  = [];

    public function init()
    {
        $this->default = [
            'year'        => date('Y'),
            'category_id' => 1,
        ];
    }

    public function login()
    {
        if ($this->isLogin()) {
            return;
        }
        echo "ACG.RIP: {$this->username} need login", PHP_EOL;
        $data = [
            'utf8'               => '✓',
            'authenticity_token' => $this->getToken('/users/sign_in'),
            'user[email]'        => $this->username,
            'user[password]'     => $this->password,
            'user[remember_me]'  => 1,
            'commit'             => '登录',
        ];

        $this->getSession()->post('/users/sign_in', null, $data);

    }

    public function upload()
    {
        $require = ['post_id', 'title', 'bangumi', 'author', 'content', 'year', 'torrent_name', 'torrent_path', 'category_id'];
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

        $coverter = new HTMLConverter($this->data['content']);

        $data = [
            'post[title]'        => $this->data['title'],
            'post[content]'      => $coverter->toBBCode(),
            'year'               => $this->data['year'],
            'post[torrent]'      => $torrent,
            'authenticity_token' => $this->getToken('/cp/posts/upload'),
            'post[category_id]'  => $this->data['category_id'],
            'commit'             => '发布',
            'utf8'               => '✓',
        ];
        $logfile = "{$this->logdir}/acgrip-{$this->data['post_id']}.log";

        // $data['series_id'] = $this->getSeries($data['year'], $this->data['bangumi']);

        if (in_array($this->data['author'], ['幻之字幕组', '幻之字幕組'])) {
            $data['post[post_as_team]'] = 1;
        }

        $hook = new \Requests_Hooks();
        $hook->register('curl.before_send', function ($ch) use ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        });
        try {
            $response = $this->getSession()->post('/cp/posts', ['Referer' => 'https://acg.rip/cp/posts/upload'], null, ['hooks' => $hook]);
            file_put_contents($logfile, $response->body . PHP_EOL, FILE_APPEND);
            $siteId = $this->getSiteId($this->data['title']);
            file_put_contents($logfile, $siteId . PHP_EOL, FILE_APPEND);
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'ACG.RIP',
                'site_id'    => $siteId,
                'log_file'   => $logfile,
                'sync_state' => 'success',
            ];
        } catch (\Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'ACG.RIP',
                'site_id'    => 0,
                'log_file'   => $logfile,
                'sync_state' => 'failed',
            ];
            file_put_contents($logfile, $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    protected function isLogin(): bool
    {
        $response = $this->getSession()->get('/ajax/session_bar');
        $dom      = $this->dealResponse($response->body);
        $ls       = $dom->find("a[href=/users/sign_out]");
        return is_array($ls) && count($ls) > 0;
    }

    private function getToken(string $url): string
    {
        $response = $this->getSession()->get($url);
        if (empty($response->body)) {
            throw new \Exception("NON Response on {$url}");
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
        $series_rsp   = $this->getSession()->get("/ajax/get_series?year={$year}");
        $series_o_lst = json_decode($series_rsp->body, 1);
        $series_lst   = \array_column($series_o_lst, 'name', 'id');
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

    public function getSiteId(string $title)
    {
        $response = $this->getSession()->get("/cp/posts");
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
}
