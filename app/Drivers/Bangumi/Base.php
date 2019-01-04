<?php
namespace App\Drivers\Bangumi;

use App\BangumiTransferLog;

abstract class Base
{
    private $session         = null;
    private $cookie_dir      = '';
    private $cookie_jar_path = '';
    protected $callbackaddr  = '';
    protected $logdir        = '';
    protected $username      = '';
    protected $password      = '';
    protected $data          = [];
    protected $callback      = [];
    protected $host          = '';
    public $cookie_jar       = null;

    public function __construct($username, $password, $callbackaddr)
    {
        if (empty($this->host)) {
            throw new \Exception('No HOST_ADDR defined');
        }
        $this->username     = $username;
        $this->password     = $password;
        $this->callbackaddr = $callbackaddr;
        // work directory build
        $this->logdir = storage_path('logs/bangumi');
        if (!is_dir($this->logdir)) {
            re_mkdir($this->logdir);
        }
        $this->cookie_dir = storage_path('app/bangumi/cookies');
        if (!is_dir($this->cookie_dir)) {
            re_mkdir($this->cookie_dir);
        }
        $class = get_called_class();
        $class = class_basename($class);

        $this->cookie_jar_path = $this->cookie_dir . '/cookie-jar-' . $class . '-' . md5($username) . '.php';

        // try to load cookie jar file
        $cookie_str = $this->loadCookieStr();
        if ($cookie_str) {
            $this->cookie_jar = unserialize($cookie_str);
        } else {
            $this->cookie_jar = new \Requests_Cookie_Jar();
        }

        if (method_exists($this, 'init')) {
            call_user_func([$this, 'init']);
        }
    }

    final protected function getSession(): \Requests_Session
    {
        if (!($this->session instanceof \Requests_Session)) {
            $options = [
                'useragent' => 'Kazami-Labs-Auto-Publish-Application',
                'cookies'   => $this->cookie_jar,
            ];
            $this->session = new \Requests_Session($this->host, [], [], $options);
        }
        return $this->session;
    }

    final public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    final public function callback()
    {
        $require = ['post_id', 'site', 'site_id', 'log_file'];
        foreach ($require as $field) {
            if (isset($this->callback[$field])) {
                continue;
            }
            throw new \Exception("Field {$field} is required in callback");
        }

        $data               = $this->callback;
        $data['sitedriver'] = \get_called_class();
        $transferLog        = new BangumiTransferLog();
        $transferLog->fill($data);
        $transferLog->save();
    }

    final public function loadCookieStr(): string
    {
        if (!is_file($this->cookie_jar_path)) {
            return '';
        }

        $cookie_str = file_get_contents($this->cookie_jar_path);
        if ($cookie_str === false) {
            return '';
        }

        return $cookie_str;

    }

    final public function __destruct()
    {
        if ($this->cookie_jar) {
            file_put_contents($this->cookie_jar_path, serialize($this->cookie_jar));
        }
    }

    abstract public function login();
    abstract protected function isLogin(): bool;
    abstract public function upload();
}
