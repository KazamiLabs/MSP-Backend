<?php

namespace App\Drivers\Bangumi;

use Converter\BBCodeConverter;
use Converter\HTMLConverter;
use HtmlParser\ParserDom;
use PHP\BitTorrent\Torrent;

class Nyaa extends Base
{
    protected $host   = 'https://nyaa.si';
    private $announce = ['http://nyaa.tracker.wf:7777/announce'];
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
        return;
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

        $logfile = "{$this->logdir}/nyaa-{$this->data['post_id']}.log";

        try {
            $this->dealTorrent($this->data['torrent_path']);
        } catch (\Throwable $t) {
            file_put_contents($logfile, $t->getMessage() . PHP_EOL, FILE_APPEND);
        }
        $torrent = new \CURLFile($this->data['torrent_path'], 'application/x-bittorrent', $this->data['torrent_name']);

        $data = [
            'torrent_data' => [
                'name'        => $this->data['title'],
                'description' => $this->data['content'],
                'category'    => '1_3',
                'information' => '',
                'trusted'     => 1,
            ],
            'torrent'      => $torrent,
        ];

        if (in_array($this->data['author'], ['幻之字幕组', '幻之字幕組'])) {
            // $data['post[post_as_team]'] = 1;
        }

        try {
            $content = $this->data['content'];
            if (strlen($content) > 10240) {
                $dom  = $this->createDomObj($content);
                $imgs = $dom->find('img');
                if (isset($imgs[0]) && $imgs[0] instanceof ParserDom) {
                    $data['torrent_data']['description'] = $imgs[0]->outerHtml() . '<p>Power by Maboroshi Sync Chan, design by Kazami Labs IT Dept.</p>';
                } else {
                    throw new \Exception('Need at least one image');
                }
            }
            $html_coverter   = new HTMLConverter($data['torrent_data']['description']);
            $bbcode_coverter = new BBCodeConverter($html_coverter->toBBCode());

            $data['torrent_data']['description'] = $bbcode_coverter->toMarkdown();
            $data['torrent_data']                = json_encode($data['torrent_data']);

            $hook = new \Requests_Hooks();
            $hook->register('curl.before_send', function ($ch) use ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            });
            $auth    = new \Requests_Auth_Basic(array($this->username, $this->password));
            $options = [
                'auth'  => $auth,
                'hooks' => $hook,
            ];
            $response = $this->getSession()->post('/api/upload', null, null, $options);
            file_put_contents($logfile, $response->body . PHP_EOL, FILE_APPEND);
            $siteId = $this->getSiteId($response->body);
            file_put_contents($logfile, $siteId . PHP_EOL, FILE_APPEND);
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'Nyaa',
                'site_id'    => $siteId,
                'log_file'   => $logfile,
                'sync_state' => 'success',
            ];
        } catch (\Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'Nyaa',
                'site_id'    => 0,
                'log_file'   => $logfile,
                'sync_state' => 'failed',
            ];
            file_put_contents($logfile, $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    protected function isLogin(): bool
    {
        return true;
    }

    private function getSiteId(string $responseBody)
    {
        $response = json_decode($responseBody);
        if ($response === false) {
            throw new \Exception("Invaild response");
        }
        return $response->id;
    }

    private function dealTorrent(string $torrentPath)
    {
        $torrent = Torrent::createFromTorrentFile($torrentPath);
        foreach ($this->announce as $announce) {
            $torrent->setAnnounce($announce);
        }
        $torrent->save($torrentPath);
    }

    private function createDomObj(string $body): ParserDom
    {
        return new ParserDom("<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>{$body}");
    }
}
