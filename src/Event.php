<?php

namespace Mix\Server;

/**
 * Class Event
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
class Event
{

    /**
     * Start
     */
    const START = 'start';

    /**
     * Shutdown
     */
    const SHUTDOWN = 'shutdown';

    /**
     * ManagerStart
     */
    const MANAGER_START = 'managerStart';

    /**
     * WorkerError
     */
    const WORKER_ERROR = 'workerError';

    /**
     * ManagerStop
     */
    const MANAGER_STOP = 'managerStop';

    /**
     * WorkerStart
     */
    const WORKER_START = 'workerStart';

    /**
     * WorkerStop
     */
    const WORKER_STOP = 'workerStop';

    /**
     * WorkerExit
     */
    const WORKER_EXIT = 'workerExit';

    /**
     * Request
     */
    const REQUEST = 'request';

    /**
     * Connect
     */
    const CONNECT = 'connect';

    /**
     * Receive
     */
    const RECEIVE = 'receive';

    /**
     * Close
     */
    const CLOSE = 'close';

    /**
     * Packet
     */
    const PACKET = 'packet';

    /**
     * HandShake
     */
    const HANDSHAKE = 'handshake';

    /**
     * Open
     */
    const OPEN = 'open';

    /**
     * Message
     */
    const MESSAGE = 'message';

}
