<?php

namespace Mix\Server;

use Mix\Server\Exception\ReceiveException;
use Swoole\Coroutine\Socket;

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
     * @var ConnectionManager
     */
    public $connectionManager;

    /**
     * Connection constructor.
     * @param SwooleConnection $connection
     * @param ConnectionManager $connectionManager
     */
    public function __construct(\Swoole\Coroutine\Server\Connection $connection, ConnectionManager $connectionManager)
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
        $socket = $this->getSwooleSocket();
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
        $fd = $this->getSwooleSocket()->fd;
        $this->connectionManager->remove($fd);
        return $this->swooleConnection->close();
    }

    /**
     * Get swoole socket
     * @return Socket
     */
    public function getSwooleSocket()
    {
        return $this->swooleConnection->socket;
    }

}
