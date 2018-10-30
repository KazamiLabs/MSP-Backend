<?php

namespace App\Console\Commands;

use App\Crontab as CrontabModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Crontab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:crontab';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理定时任务队列';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $start_time = strtotime(date("Y-m-d H:i:00"));
        $end_time   = $start_time + 60;
        $options    = [
            'useragent' => 'Kazami-Labs-Auto-Response-System',
        ];
        $r_session = new \Requests_Session(config('qqbot.coolq_http_api'), [], [], $options);
        $req_data  = [
            'group_id'    => '',
            'message'     => '',
            'auto_escape' => 'false',
        ];
        // echo $start_time, ',', $end_time, PHP_EOL;
        CrontabModel::where('status', 2)
            ->whereBetween('notice_timestamp', [$start_time, $end_time])
            ->chunk(10, function ($crontabs) use ($req_data, $r_session) {
                foreach ($crontabs as $crontab) {
                    $req_data['group_id'] = $crontab->src_group_id;
                    $req_data['message']  = "[CQ:at,qq={$crontab->user_id}] 记得啦！【{$crontab->todo}】";
                    // var_export($req_data);
                    try {
                        $r_session->post('/send_group_msg', [], $req_data);
                        $crontab->status = '1';
                        $crontab->save();

                    } catch (\Throwable $t) {
                        Log::alert($t->getMessage());
                    }

                }
            });

    }
}
