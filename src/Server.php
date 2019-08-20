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
     * @var Server
     */
    public $swooleServer;

    /**
     * @var ConnectionManager
     */
    public $connectionManager;

    /**
     * HttpServer constructor.
     * @param string $host
     * @param int $port
     * @param bool $ssl
     */
    public function __construct(string $host, int $port, bool $ssl)
    {
        $this->host              = $host;
        $this->port              = $port;
        $this->ssl               = $ssl;
        $this->swooleServer      = new \Swoole\Coroutine\Server($host, $port, $ssl);
        $this->connectionManager = new ConnectionManager();
    }

    /**
     * Set
     * @param array $options
     */
    public function set(array $options)
    {
        return $this->swooleServer->set($options);
    }

    /**
     * Handle
     * @param callable $callback
     */
    public function handle(callable $callback)
    {
        return $this->swooleServer->handle(function (\Swoole\Coroutine\Server\Connection $connection) use ($callback) {
            try {
                // 生成连接
                $connection = new Connection($connection, $this->connectionManager);
                $fd         = $connection->swooleSocket->fd;
                $this->connectionManager->add($fd, $connection);
                // 执行回调
                call_user_func($callback, $connection);
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
    }

    /**
     * Start
     */
    public function start()
    {
        return $this->swooleServer->start();
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
