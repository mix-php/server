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
     * @var Connection[]
     */
    protected $connections = [];

    /**
     * 新增连接
     * @param int $fd
     * @param Connection $connection
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
     * 计数
     * @return int
     */
    public function count()
    {
        return count($this->connections);
    }

    /**
     * 关闭全部连接
     */
    public function closeAll()
    {
        foreach ($this->connections as $fd => $connection) {
            $connection->close();
            $this->remove($fd);
        }
    }

    /**
     * 获取全部连接
     * @return Connection[]
     */
    public function getConnections()
    {
        return array_values($this->connections);
    }

}
