<?php

namespace Mix\Server;

use Mix\Core\Bean\AbstractObject;
use Mix\Helper\ProcessHelper;

/**
 * Class AbstractServer
 * @package Mix\Server
 * @author liu,jian <coder.keda@gmail.com>
 */
abstract class AbstractServer extends AbstractObject
{

    /**
     * 服务器名称
     * @var string
     */
    public $name = '';

    /**
     * 服务器版本
     * @var string
     */
    public $version = '';

    /**
     * 主机
     * @var string
     */
    public $host;

    /**
     * 端口
     * @var int
     */
    public $port;

    /**
     * 应用配置文件 (为了兼容旧版本，保留这项配置)
     * @var string
     */
    public $configFile = '';

    /**
     * 应用配置信息
     * @var array
     */
    public $config = [];

    /**
     * 运行参数
     * @var array
     */
    public $setting = [];

    /**
     * 服务器
     * @var \Swoole\Server
     */
    public $server;

    /**
     * 初始化事件
     */
    public function onInitialize()
    {
        parent::onInitialize(); // TODO: Change the autogenerated stub
        // 快捷引用
        \Mix::$server = $this;
        // 载入配置文件
        if ($this->configFile != '') {
            $this->config = require $this->configFile;
        }
    }

    /**
     * 主进程启动事件
     * 仅允许echo、打印Log、修改进程名称，不得执行其他操作
     * @param \Swoole\Server $server
     */
    public function onStart(\Swoole\Server $server)
    {
        // 进程命名
        ProcessHelper::setProcessTitle($this->name . ": master {$this->host}:{$this->port}");
    }

    /**
     * 主进程停止事件
     * 请勿在onShutdown中调用任何异步或协程相关API，触发onShutdown时底层已销毁了所有事件循环设施
     * @param \Swoole\Server $server
     */
    public function onShutdown(\Swoole\Server $server)
    {
        try {

            // 执行回调
            $this->setting['hook_shutdown'] and call_user_func($this->setting['hook_shutdown'], $server);

        } catch (\Throwable $e) {
            // 错误处理
            \Mix::$app->error->handleException($e);
        }
    }

    /**
     * 管理进程启动事件
     * 可以使用基于信号实现的同步模式定时器swoole_timer_tick，不能使用task、async、coroutine等功能
     * @param \Swoole\Server $server
     */
    public function onManagerStart(\Swoole\Server $server)
    {
        try {

            // 进程命名
            ProcessHelper::setProcessTitle($this->name . ": manager");
            // 执行回调
            $this->setting['hook_manager_start'] and call_user_func($this->setting['hook_manager_start'], $server);

        } catch (\Throwable $e) {
            // 错误处理
            \Mix::$app->error->handleException($e);
        }
    }

    /**
     * 工作进程错误事件
     * 当Worker/Task进程发生异常后会在Manager进程内回调此函数。
     * @param \Swoole\Server $server
     */
    public function onWorkerError(\Swoole\Server $server, int $workerId, int $workerPid, int $exitCode, int $signal)
    {
        try {

            // 执行回调
            $this->setting['hook_worker_error'] and call_user_func($this->setting['hook_worker_error'], $server, $workerId, $workerPid, $exitCode, $signal);

        } catch (\Throwable $e) {
            // 错误处理
            \Mix::$app->error->handleException($e);
        }
    }

    /**
     * 管理进程停止事件
     * @param \Swoole\Server $server
     */
    public function onManagerStop(\Swoole\Server $server)
    {
        try {

            // 执行回调
            $this->setting['hook_manager_stop'] and call_user_func($this->setting['hook_manager_stop'], $server);

        } catch (\Throwable $e) {
            // 错误处理
            \Mix::$app->error->handleException($e);
        }
    }

    /**
     * 工作进程停止事件
     * @param \Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerStop(\Swoole\Server $server, int $workerId)
    {
        try {

            // 执行回调
            $this->setting['hook_worker_stop'] and call_user_func($this->setting['hook_worker_stop'], $server);

        } catch (\Throwable $e) {
            // 错误处理
            \Mix::$app->error->handleException($e);
        }
    }

    /**
     * 工作进程退出事件
     * 仅在开启reload_async特性后有效。异步重启特性，会先创建新的Worker进程处理新请求，旧的Worker进程自行退出
     * @param \Swoole\Server $server
     */
    public function onWorkerExit(\Swoole\Server $server, int $workerId)
    {
        try {

            // 执行回调
            $this->setting['hook_worker_exit'] and call_user_func($this->setting['hook_worker_exit'], $server, $workerId);

        } catch (\Throwable $e) {
            // 错误处理
            \Mix::$app->error->handleException($e);
        }
    }

    /**
     * 欢迎信息
     */
    protected function welcome()
    {
        $swooleVersion = swoole_version();
        $phpVersion    = PHP_VERSION;
        echo <<<EOL
                             _____
_______ ___ _____ ___   _____  / /_  ____
__/ __ `__ \/ /\ \/ /__ / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ _ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\  / .___/_/ /_/ .___/
                     /_/         /_/


EOL;
        println('APP            Name:      ' . $this->config['appName']);
        println('APP            Version:   ' . $this->config['appVersion']);
        println('Server         Name:      ' . $this->name);
        println('Server         Version:   ' . $this->version);
        println('System         Name:      ' . strtolower(PHP_OS));
        println("PHP            Version:   {$phpVersion}");
        println("Swoole         Version:   {$swooleVersion}");
        println('Framework      Version:   ' . \Mix::$version);
        $this->setting['max_request'] == 1 and println('Hot            Update:    enabled');
        $this->setting['enable_coroutine'] and println('Coroutine      Mode:      enabled');
        println("Listen         Addr:      {$this->host}");
        println("Listen         Port:      {$this->port}");
        $this->configFile and println("Configuration  File:      {$this->configFile}");
    }

}
