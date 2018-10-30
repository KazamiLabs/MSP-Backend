<?php

namespace App\Providers;

use App\Crontab;
use App\Tool\Time\Convert;
use Illuminate\Support\ServiceProvider;

class BotServiceProvider extends ServiceProvider
{
    private $r_session = null;
    private $profile   = [
        'name'    => '更新姬',
        'user_id' => '1283024365',
    ];
    private $req_data = [
        'group_id'    => '',
        'message'     => '',
        'auto_escape' => 'false',
    ];

    private $auth_group = [
        // '549640247',
        '857053153',
        '558170615',
    ];
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        // $this->options['src_group_id'] = $options['src_group_id'];
        // $this->req_data['group_id']    = $options['src_group_id'];
        $options = [
            'useragent' => 'Kazami-Labs-Auto-Response-System',
        ];
        $this->r_session = new \Requests_Session(config('qqbot.coolq_http_api'), [], [], $options);

    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function help()
    {
        $this->req_data['message'] = <<<EOF
在哦 请问有何吩咐呢~
[{$this->profile['name']} 介绍一下]
[百科幻之]
[{$this->profile['name']} 在明天8点提醒我去和女朋友约会~]
未来或许会学会的技能
[{$this->profile['name']} 我要看动漫]
学不学得会呢~看缘分吧
每人每5分钟内只可以应答3次
利用时请保持节制 刷屏的话{$this->profile['name']}就要被禁言了
EOF;
        return $this->exSend();
    }

    public function init()
    {
        $this->req_data['message'] = <<<EOF
正在载入更新姬人工智障应答程式~
当前版本：开发者预览版，支援以下文字指令：
[{$this->profile['name']} 介绍一下]
[百科幻之]
[{$this->profile['name']} 在明天8点提醒我去和女朋友约会~]
如果你忘记了，@一下更新姬就可以重新看使用提示啦~
还没有接入图灵程式，对话什么的……做梦吧，梦里什么都有
利用时请保持节制 刷屏的话{$this->profile['name']}就要被禁言了
EOF;
        return $this->exSend();
    }

    public function welcome()
    {
        $this->req_data['message'] = "各位好 欢迎大驾光临幻之字幕组 在下是侍从于此的机器人女仆{$this->profile['name']} 请多指教~此处是一个文化交流的场所 希望大家可以一起维持一个友善而愉快的氛围";
        return $this->exSend();
    }

    public function notice($data)
    {
        $regx_lst = [
            '/在(?<natual_time>.+?)提醒我去(?<todo>.+)/',
            '/在(?<natual_time>.+?)提醒我(?<todo>.+)/',
            '/在(?<natual_time>.+?)叫我去(?<todo>.+)/',
            '/在(?<natual_time>.+?)叫我(?<todo>.+)/',
            '/提醒我(?<natual_time>.+?)去做(?<todo>.+)/',
            '/提醒我(?<natual_time>.+?)去(?<todo>.+)/',
            '/提醒我(?<natual_time>.+?)做(?<todo>.+)/',
        ];

        foreach ($regx_lst as $regx) {
            $matches = [];
            if (preg_match($regx, $data['raw_message'], $matches)) {
                break;
            }
        }
        // Log::record(json_encode($matches, JSON_UNESCAPED_UNICODE), 'tks-debug-feedback');
        if (isset($matches['natual_time']) && isset($matches['todo'])) {
            $time       = new Convert($matches['natual_time']);
            $timestamp  = $time->getTimestamp();
            $datetime   = date('Y-m-d H:i:s', $timestamp);
            $s_datetime = date('Y-m-d H:i:00', $timestamp);
            $crontab    = new Crontab();
            // save cron
            $crontab->todo             = $matches['todo'];
            $crontab->user_id          = $data['user_id'];
            $crontab->src_group_id     = $data['group_id'];
            $crontab->notice_time      = $datetime;
            $crontab->notice_timestamp = $timestamp;
            $crontab->create_time      = time();
            $crontab->status           = '1';
            $crontab->save();
            $this->req_data['message'] = "[开发者预览模式]：[CQ:at,qq={$data['user_id']}] 识别出的时间是【{$matches['natual_time']}】，转换为标准时间是【{$s_datetime}】，要提醒的事情是【{$matches['todo']}】";
            return $this->exSend();
        } else {
            return;
        }

    }

    // 记录使用者频次
    public function record($data)
    {
        if (!isset($data['user_id'])) {
            throw new \Exception("Error Not User ID", 1);
        }
        $record = DB::table('user_records')->where([
            'user_id'      => $data['user_id'],
            'src_group_id' => $this->src_group_id,
        ])->find();
        if ($record && $record['count'] > 3) {
            throw new \Exception("User: {$data['user_id']}, Group: {$this->src_group_id}", 1);
        } elseif ($record && $record['count'] == 0) {
            $record['count']       = 1;
            $record['create_time'] = time();
            DB::table('user_records')->update($record);
        } elseif ($record) {
            $record['count'] = intval($record['count']) + 1;
            DB::table('user_records')->update($record);
        } else {
            $record = [
                'user_id'      => $data['user_id'],
                'src_group_id' => $this->src_group_id,
                'count'        => 1,
                'create_time'  => time(),
            ];
            DB::table('user_records')->insert($record);
        }

    }

    private function exSend()
    {
        if (!isset($this->req_data['src_group_id'])) {
            throw new \Exception("Source Group ID Not Set", 1);
        }
        if (!in_array($this->req_data['src_group_id'], $this->auth_group)) {
            throw new \Exception("Not Auth Source Group ID", 2);
        }
        return $this->r_session->post('/send_group_msg', [], $this->req_data);
    }
}
