<?php
namespace App\Services\Logs;

use App\Events\Logs\LogMonologEvent;
use App\SysLog;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class LogHandler extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }
    protected function write(array $record)
    {
        // Simple store implementation
        $log = new SysLog();
        // var_export($record);
        $log->fill($record['formatted']);
        $log->save();
        // Queue implementation
        // event(new LogMonologEvent($record));
    }
    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new LogFormatter();
    }
}
