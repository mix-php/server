<?php

namespace Mix\Server;

use Mix\Server\Exception\ReceiveException;
use Mix\Socket\Socket;

/**
 * Class Connection
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Connection
{

    /**
     * @var Connection
     */
    public $swooleConnection;

    /**
     * @var TcpConnectionManager
     */
    public $connectionManager;

    /**
     * TcpConnection constructor.
     * @param SwooleConnection $connection
     * @param TcpConnectionManager $connectionManager
     */
    public function __construct(\Swoole\Coroutine\Server\Connection $connection, TcpConnectionManager $connectionManager)
    {
        $this->swooleConnection  = $connection;
        $this->connectionManager = $connectionManager;
    }

    /**
     * Recv
     * @return mixed
     */
    public function recv()
    {
        $data   = $this->swooleConnection->recv();
        $socket = $this->getSocket();
        if ($socket->errCode != 0 || $socket->errMsg != '') {
            $this->close();
            throw new ReceiveException($socket->errMsg, $socket->errCode);
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
        $fd = $this->getSocket()->fd;
        $this->connectionManager->remove($fd);
        return $this->swooleConnection->close();
    }

    /**
     * Get socket
     * @return Socket
     */
    public function getSocket()
    {
        return $this->swooleConnection->socket;
    }

}
