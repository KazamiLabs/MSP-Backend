<?php
namespace App\Drivers\Bangumi;

use Exception;
use App\BangumiTransferLog;

abstract class Base
{
    protected $data      = [];
    protected $callback  = [];
    protected $logBuffer = '';

    /**
     * 映射设置的属性到 $data
     *
     * @param string $name
     * @param mixed $value
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-05-31
     */
    final public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * 上传完毕的统一调用
     *
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-05-31
     */
    final public function callback()
    {
        $require = ['post_id', 'site', 'site_id'];
        foreach ($require as $field) {
            if (isset($this->callback[$field])) {
                continue;
            }
            throw new Exception("Field {$field} is required in callback");
        }

        $data = $this->callback;

        $transferLog = new BangumiTransferLog();
        $transferLog->fill($data);
        // 补充必要的日志数据数据
        $transferLog->sitedriver = get_called_class();
        $transferLog->log        = $this->logBuffer;
        $transferLog->save();
    }

    /**
     * 日志记录
     * 简单记录，仅用于前端显示
     *
     * @param string $message
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-05-31
     */
    final protected function logInfo(string $message)
    {
        $this->logBuffer .= $message . PHP_EOL;
    }

    abstract public function upload();
}
