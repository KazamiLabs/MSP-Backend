<?php

namespace App\Drivers\Bangumi;

use App\Drivers\Bangumi\Base;
use CURLFile;
use Exception;
use HtmlParser\ParserDom;
use Illuminate\Support\Facades\Log;
use PHP\BitTorrent\Torrent;
use Requests_Auth_Basic;
use Requests_Hooks;
use Requests_Session;
use Throwable;

class Nyaa extends Base
{
    const HOST = 'https://nyaa.si';

    private $announce = ['http://nyaa.tracker.wf:7777/announce'];
    private $default  = [];
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

        $session->url  = self::HOST;
        $this->session = $session;

    }

    public function upload()
    {
        // 数据校验
        if (!$this->validate([
            'post_id'      => 'required|integer',
            'title'        => 'required|min:1',
            'bangumi'      => 'required|min:1',
            'author'       => 'required|min:1',
            'content_md'      => 'required|min:1',
            'torrent_name' => 'required|min:1',
            'torrent_path' => 'required|min:1',
        ])) {
            throw new Exception($this->validateErrors()->first());
        }

        if (!is_file($this->data['torrent_path'])) {
            throw new Exception("Torrent [{$this->data['torrent_path']}] isn't validate file");
        }

        try {
            $this->dealTorrent($this->data['torrent_path']);
        } catch (Throwable $t) {
            Log::warning("Nyaa: 处理种子文件失败. {$t->getMessage()}");
            $this->logInfo('Nyaa: 处理种子文件失败');
            $this->logInfo($t->getMessage());
        }
        $torrent = new CURLFile(
            $this->data['torrent_path'],
            'application/x-bittorrent',
            $this->data['torrent_name']
        );

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

        try {
            $content = $this->data['content'];
            if (strlen($content) > 10240) {
                $dom  = $this->createDomObj($content);
                $imgs = $dom->find('img');
                if (isset($imgs[0]) && $imgs[0] instanceof ParserDom) {
                    $data['torrent_data']['description'] = $imgs[0]->outerHtml() . '<p>Published by Mabors Publish Platform, design by Kazami Labs IT Dept.</p>';
                } else {
                    throw new Exception('Need at least one image');
                }
            }

            $data['torrent_data']['description'] = $this->data['content_md'];
            // 编码 torrent_data
            $data['torrent_data'] = json_encode($data['torrent_data']);

            $hook = new Requests_Hooks();
            $hook->register('curl.before_send', function ($ch) use ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            });
            // 注入鉴权信息
            $auth    = new Requests_Auth_Basic(array($this->username, $this->password));
            $options = [
                'auth'  => $auth,
                'hooks' => $hook,
            ];

            $response = $this->session->post('/api/upload', null, null, $options);
            Log::info("Nyaa: 响应", [$response]);
            $this->logInfo($response->body);

            $siteId = $this->getSiteId($response->body);
            Log::info("Nyaa: 种子ID: {$siteId}");
            $this->logInfo($siteId);

            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'Nyaa',
                'site_id'    => $siteId,
                'sync_state' => 'success',
            ];
        } catch (Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => 'Nyaa',
                'site_id'    => 0,
                'sync_state' => 'failed',
            ];
            $this->logInfo($e->getMessage());
            $this->callback();

            Log::error("Nyaa: 上传异常. {$e->getMessage()}");

            throw $e;
        }
    }

    private function getSiteId(string $responseBody)
    {
        $response = json_decode($responseBody);
        if ($response === false) {
            throw new Exception("Invaild response");
        }
        if (isset($response->id)) {
            $id = $response->id;
        } elseif (
            isset($response->errors) &&
            isset($response->errors->torrent) &&
            isset($response->errors->torrent[0])
        ) {
            $matches = [];
            if (preg_match(
                '/This\storrent\salready\sexists\s*\(#(\d+)\)/',
                $response->errors->torrent[0],
                $matches
            )) {
                $id = $matches[1];
            } else {
                throw new Exception(json_encode($response->errors));
            }
        } else {
            throw new Exception(json_encode($response->errors));
        }
        return $id;
    }

    private function dealTorrent(string $torrentPath)
    {
        $torrent = Torrent::createFromTorrentFile($torrentPath);
        foreach ($this->announce as $announce) {
            $torrent->setAnnounce($announce);
        }
        $torrent->save($torrentPath);
    }
}
