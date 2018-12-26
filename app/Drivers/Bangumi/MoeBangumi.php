<?php
namespace App\Drivers\Bangumi;

class MoeBangumi extends Base
{
    protected $host      = 'https://bangumi.moe';
    private $myteam      = [];
    private $deal_myteam = [];

    public function login()
    {
        if ($this->isLogin()) {
            return;
        }
        echo "Moe Bangumi: {$this->username} need login", PHP_EOL;
        $data = [
            'username' => $this->username,
            'password' => md5($this->password),
        ];

        $header = [
            'Content-Type' => 'application/json',
        ];

        $data_raw = json_encode($data, JSON_UNESCAPED_UNICODE);

        $response     = $this->getSession()->post('/api/user/signin', $header, $data_raw);
        $login_result = json_decode($response->body, true);
        if ($login_result['success'] === false) {
            throw new \Exception("Login failed! {$response->body}");
        }

    }

    public function upload()
    {
        $require = ['author', 'title', 'content', 'torrent_name', 'torrent_path'];
        foreach ($require as $field) {
            if (!isset($this->data[$field])) {
                throw new \Exception("Field {$field} is required");
            }
        }
        $logfile = "{$this->logdir}/moe-bangumi-{$this->data['post_id']}.log";

        if (!is_file($this->data['torrent_path'])) {
            throw new \Exception("Torrent [{$this->data['torrent_path']}] isn't validate file");
        }
        $torrent = new \CURLFile($this->data['torrent_path'], 'application/octet-stream', $this->data['torrent_name']);

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
        $response = $this->getSession()->post('/api/v2/torrent/upload', null, null, ['hooks' => $hook]);
        file_put_contents($logfile, $response->body . PHP_EOL, FILE_APPEND);
        $torrent_res = json_decode($response->body, true);
        if ($torrent_res['success'] === false) {
            throw new \Exception("Upload failed. {$response->body}");
        }

        $data = [
            'category_tag_id' => '549ef250fe682f7549f1ea91',
            'title'           => $this->data['title'],
            'introduction'    => $this->data['content'],
            'tag_ids'         => [],
            'team_id'         => $team_id,
            'file_id'         => $torrent_res['file_id'],
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
            $response = $this->getSession()->post('/api/torrent/add', $header, $data_raw);
            file_put_contents($logfile, $response->body . PHP_EOL, FILE_APPEND);
            $data           = json_decode($response->body);
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '萌番组',
                'site_id'    => $data->torrent->_id,
                'log_file'   => $logfile,
                'sync_state' => 'success',
            ];
        } catch (\Exception $e) {
            $this->callback = [
                'post_id'    => $this->data['post_id'],
                'site'       => '萌番组',
                'site_id'    => 0,
                'log_file'   => $logfile,
                'sync_state' => 'failed',
            ];
            file_put_contents($logfile, $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    protected function isLogin(): bool
    {
        $response = $this->getSession()->get('/api/user/session');
        $data     = json_decode($response->body, true);
        return isset($data['username']) && $data['username'] == $this->username;
    }

    private function getTeamData()
    {
        $team_resp = $this->getSession()->get('/api/team/myteam');
        $team      = json_decode($team_resp->body, true);
        if (is_array($team) && count($team) > 0) {
            $this->myteam      = $team;
            $this->deal_myteam = array_column($this->myteam, '_id', 'name');
        }
    }
}
