<?php

namespace Mix\Server;

use Mix\Server\Exception\ReceiveException;
use Mix\Server\Exception\SendException;

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
            throw new ReceiveException($socket->errMsg, $socket->errCode);
        }
        if ($data === "") { // 连接关闭
            $this->close();
            $errCode = 104;
            $errMsg  = swoole_strerror($errCode, 9);
            throw new ReceiveException($errMsg, $errCode);
        }
        return $data;
    }

    /**
     * Send
     * @param string $data
     * @return bool
     */
    public function send(string $data)
    {
        $len  = strlen($data);
        $size = $this->swooleConnection->send($data);
        if ($size === false) {
            throw new SendException($this->swooleConnection->socket->errMsg, $this->swooleConnection->socket->errCode);
        }
        if ($len !== $size) {
            throw new SendException('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
        return true;
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
