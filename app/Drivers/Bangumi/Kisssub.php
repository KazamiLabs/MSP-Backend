<?php
namespace App\Drivers\Bangumi;

class Kisssub extends Base
{
    protected $host      = 'http://www.kisssub.org';
    private $myteam      = [];
    private $deal_myteam = [];

    public function login()
    {
        return true;
    }

    public function upload()
    {
        $require = ['author', 'title', 'content', 'torrent_name', 'torrent_path'];
        foreach ($require as $field) {
            if (!isset($this->data[$field])) {
                throw new \Exception("Field {$field} is required");
            }
        }
        $logfile = "{$this->logdir}/kisssub-{$this->data['post_id']}.log";

        if (!is_file($this->data['torrent_path'])) {
            throw new \Exception("Torrent [{$this->data['torrent_path']}] isn't validate file");
        }
        $torrent = new \CURLFile($this->data['torrent_path'], 'application/octet-stream', $this->data['torrent_name']);

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

        $hook = new \Requests_Hooks();
        $hook->register('curl.before_send', function ($ch) use ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        });
        $options = [
            'hooks' => $hook,
        ];

        try {
            $response = $this->getSession()->post('/addon.php?r=api/post/76cad81b', null, null, $options);
            file_put_contents($logfile, $response->body . PHP_EOL, FILE_APPEND);
            $data           = json_decode($response->body);
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '爱恋动漫',
                'site_id'    => $data->info_hash,
                'log_file'   => $logfile,
                'sync_state' => $data->status,
            ];
        } catch (\Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '爱恋动漫',
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
}
