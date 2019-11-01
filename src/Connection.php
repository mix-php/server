<?php

namespace Mix\Server;

use Mix\Server\Exception\ReceiveException;

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
     * @return string
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
     */
    public function send(string $data)
    {
        $len  = strlen($data);
        $size = $this->swooleConnection->send($data);
        if ($size === false) {
            throw new \Swoole\Exception($this->swooleConnection->socket->errMsg, $this->swooleConnection->socket->errCode);
        }
        if ($len !== $size) {
            throw new \Swoole\Exception('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
    }

    /**
     * Close
     */
    public function close()
    {
        if (!$this->swooleConnection->close()) {
            $errMsg  = $this->swooleConnection->socket->errMsg;
            $errCode = $this->swooleConnection->socket->errCode;
            if ($errMsg == '' && $errCode == 0) {
                return;
            }
            if ($errMsg == 'Connection reset by peer' && $errCode == 104) {
                return;
            }
            throw new \Swoole\Exception($errMsg, $errCode);
        }
        $this->connectionManager->remove($this->swooleSocket->fd);
    }

}
