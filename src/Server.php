<?php

namespace Mix\Server;

/**
 * Class Server
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Server
{

    /**
     * @var string
     */
    public $host = '127.0.0.1';

    /**
     * @var int
     */
    public $port = 9503;

    /**
     * @var bool
     */
    public $ssl = false;

    /**
     * @var bool
     */
    public $reusePort = false;

    /**
     * @var ConnectionManager
     */
    public $connectionManager;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @var Server
     */
    public $swooleServer;

    /**
     * Server constructor.
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @param bool $reusePort
     */
    public function __construct(string $host, int $port, bool $ssl = false, bool $reusePort = false)
    {
        $this->host              = $host;
        $this->port              = $port;
        $this->ssl               = $ssl;
        $this->reusePort         = $reusePort;
        $this->connectionManager = new ConnectionManager();
    }

    /**
     * Set
     * @param array $options
     */
    public function set(array $options)
    {
        $this->options = $options;
    }

    /**
     * Handle
     * @param callable $callback
     */
    public function handle(callable $callback)
    {
        $this->handler = $callback;
    }

    /**
     * Start
     */
    public function start()
    {
        $server = $this->swooleServer = new \Swoole\Coroutine\Server($this->host, $this->port, $this->ssl, $this->reusePort);
        $server->set($this->options);
        $server->handle(function (\Swoole\Coroutine\Server\Connection $connection) {
            try {
                // 生成连接
                $connection = new Connection($connection, $this->connectionManager);
                $fd         = $connection->swooleSocket->fd;
                $this->connectionManager->add($fd, $connection);
                // 执行回调
                call_user_func($this->handler, $connection);
            } catch (\Throwable $e) {
                $isMix = class_exists(\Mix::class);
                // 错误处理
                if (!$isMix) {
                    throw $e;
                }
                // Mix错误处理
                /** @var \Mix\Console\Error $error */
                $error = \Mix::$app->context->get('error');
                $error->handleException($e);
            }
        });
        return $server->start();
    }

    /**
     * Shutdown
     */
    public function shutdown()
    {
        $status = $this->swooleServer->shutdown();
        $this->connectionManager->closeAll();
        return $status;
    }

}
