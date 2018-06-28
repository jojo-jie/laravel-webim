<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class Websocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket {arguments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'websocket';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    private $server;
    private $host;
    private $port;
    private $table;
    private $count;//在线人数

    public function __construct()
    {
        parent::__construct();
        $this->host = config('swoole.host');
        $this->port = config('swoole.port');
        $this->createTable();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('arguments');
        switch ($type) {
            case 'start':
                $this->doTask();
                break;
            case 'drop':
                $this->drop();//清空内存表
                break;
        }
    }

    public function doTask()
    {
        $this->server = new \swoole_websocket_server($this->host, $this->port);
        $this->server->on('open',[$this,'open']);
        $this->server->on('message',[$this,'message']);
        $this->server->on('close',[$this,'close']);
        $this->server->start();
    }

    public function open(\swoole_websocket_server $server, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    public function message(\swoole_websocket_server $server, $frame)
    {
        $data = json_decode($frame->data,true);
        if ($data['type'] == 'open') {
            $user_info = Redis::hgetall('user_id:'.$data['id']);
            $user = [
                'fd' => $frame->fd,
                'id' => $user_info['id'],
                'name' => $user_info['name'],
                'avatar' => $user_info['avatar']
            ];
            $this->table->set($frame->fd, $user);
            $this->count = $this->table->count();
            $this->pushMessage($server, $user_info['name'].'进入聊天室', 'open', $frame->fd);
        } else {
            $this->pushMessage($server, $frame->data, 'message', $frame->fd);
        }
    }

    private function pushMessage($server, $message, $messageType, $frameFd)
    {
        $message = htmlspecialchars($message);
        $datetime = date('Y-m-d H:i:s', time());
        $user = $this->table->get($frameFd);
        if ($messageType == 'open') {
            $server->push($frameFd, json_encode(
                    array_merge(['user' => $user], ['all' => $this->allUser()], ['type' => 'openSuccess'], ['count' => $this->count])
                )
            );
        }

        foreach ($this->table as $row) {
            if ($frameFd == $row['fd']) {
                continue;
            }
            $server->push($row['fd'], json_encode([
                    'type' => $messageType,
                    'message' => $message,
                    'datetime' => $datetime,
                    'user' => $user,
                    'count'=> $this->count
                ])
            );
        }
    }

    private function allUser()
    {
        $users = [];
        foreach ($this->table as $row) {
            $users[] = $row;
        }
        return $users;
    }

    public function close(\swoole_websocket_server $server, $fd)
    {
        echo "client {$fd} closed\n";
        $user = $this->table->get($fd);
        $this->count = $this->count-1;
        $this->pushMessage($server, $user['name']."离开聊天室", 'close', $fd);
        $this->table->del($fd);
    }

    /**
     * 创建内存表
     */
    private function createTable()
    {
        $this->table = new \swoole_table(1024);
        $this->table->column('fd', \swoole_table::TYPE_INT);
        $this->table->column('id', \swoole_table::TYPE_INT);
        $this->table->column('name', \swoole_table::TYPE_STRING, 255);
        $this->table->column('avatar', \swoole_table::TYPE_STRING, 255);
        $this->table->create();
    }

    private function drop()
    {
        foreach ($this->table as $row){
            $this->table->del($row['fd']);
        }
        echo '========================';
    }




}
