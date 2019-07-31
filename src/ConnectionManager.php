<?php

namespace Mix\Server;

/**
 * Class ConnectionManager
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class ConnectionManager
{

    /**
     * @var TcpConnection[]
     */
    protected $connections = [];

    /**
     * 新增连接
     * @param int $fd
     * @param TcpConnection $connection
     */
    public function add(int $fd, Connection $connection)
    {
        $this->connections[$fd] = $connection;
    }
    
    /**
     * 移除连接
     * @param int $fd
     */
    public function remove(int $fd)
    {
        unset($this->connections[$fd]);
    }

    /**
     * 关闭全部连接
     */
    public function closeAll()
    {
        foreach ($this->connections as $connection) {
            $connection->close();
        }
    }

    /**
     * 获取全部连接
     * @return TcpConnection[]
     */
    public function getConnections()
    {
        return array_values($this->connections);
    }

}
