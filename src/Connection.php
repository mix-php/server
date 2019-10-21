<?php

namespace Mix\Server;

use Mix\Server\Exception\ReceiveFailureException;

/**
 * Class Connection
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection
{

    /**
     * @var \Swoole\Coroutine\Server\Connection
     */
    protected $swooleConnection;

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var \Swoole\Coroutine\Socket
     */
    public $swooleSocket;

    /**
     * Connection constructor.
     * @param \Swoole\Coroutine\Server\Connection $connection
     * @param ConnectionManager $connectionManager
     */
    public function __construct(\Swoole\Coroutine\Server\Connection $connection, ConnectionManager $connectionManager)
    {
        $this->swooleConnection  = $connection;
        $this->connectionManager = $connectionManager;
        $this->swooleSocket      = $connection->socket;
    }

    /**
     * Recv
     * @return mixed
     */
    public function recv()
    {
        $data = $this->swooleConnection->recv();
        if ($data === false) { // 接收失败
            $this->close();
            $socket = $this->swooleSocket;
            throw new ReceiveFailureException($socket->errMsg, $socket->errCode);
        }
        if ($data === "") { // 连接关闭
            $this->close();
            $errCode = 104;
            $errMsg  = swoole_strerror($errCode, 9);
            throw new ReceiveFailureException($errMsg, $errCode);
        }
        return $data;
    }

    /**
     * Send
     * @param $data
     * @return bool
     */
    public function send($data)
    {
        return $this->swooleConnection->send($data);
    }

    /**
     * Close
     * @return bool
     */
    public function close()
    {
        $fd = $this->swooleSocket->fd;
        $this->connectionManager->remove($fd);
        return $this->swooleConnection->close();
    }

}
