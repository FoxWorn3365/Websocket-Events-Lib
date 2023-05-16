<?php

namespace FoxWorn3365\WebSocketEvents;

use FoxWorn3365\SocketManager\SocketClient;
use FoxWorn3365\SocketManager\SharedMemoryClient;
use FoxWorn3365\WebSocketEvents\Parts\Player;
use FoxWorn3365\WebSocketEvents\Parts\Entity;
use FoxWorn3365\WebSocketEvents\Parts\Item;
use FoxWorn3365\WebSocketEvents\Parts\Block;
use FoxWorn3365\WebSocketEvents\Parts\Server;

class Core {
    // @ description: The raw php socket
    // @ note: The type is "resource" but is kinda of deprecated
    protected $socket;
    // @ description: The socket client
    protected SocketClient $client;
    // @ description: Hostname of the websocket server
    protected string $host;
    // @ description: Port of the websocket server
    protected int $port;
    // @ description: Password (or token) of the websocket server
    protected string $token;
    // @ description: Timeout of the connection
    // @ note: You must define this by $class->timeout = int
    public int $timeout = 5;
    // @ description: The child (heartbeat) PID
    protected int $pid;
    // @ description: The object where all callbacks are stored
    protected object $callbacks;
    // @ description: All available events are listed here
    protected array $events = [
        'player_quit',
        'player_move',
        'player_hit',
        'entity_hit',
        'player_hurt',
        'entity_hurt',
        'player_item_use',
        'block_break',
        'block_place',
        'block_update',
        'player_join',
        'player_login',
        'player_bed_enter',
        'player_bed_leave',
        'player_bloock_pick',
        'player_chat',
        'player_drop_item',
        'player_jump',
        'player_kick',
        'player_respawn',
        'player_death',
        'ready'
    ];
    // @ description: You can run this asyncronously via pcntl (like heartbeat)
    // @ note: This feature is in BETA, please don't rely on it in production
    // @ warning: As this feature is in BETA you can witness events such as crashes or processes that are not terminated.<br>We are working hard to make this a stable function.
    public bool $async = false;
    // @ description: All child process will be put here if class:$async is true
    public array $childs = [];
    // @ description: Whether or not the client is in the loop
    protected bool $execution = true;
    // @ description: The server data shared memory ID
    protected int $shared = 491094;
    // @ description: The shared memory client class
    protected SharedMemoryClient $memory;

    /*
    * @ description: The construct class, here you must define some connectivity options
    * @ param: The host. Use "localhost" if you're in the same machine
    * @ param: The port
    * @ param: The access token defined in the plugin configuration
    */
    function __construct(string $host, int $port, string $token) {
        $this->callbacks = new \stdClass;
        $this->host = $host;
        $this->port = $port;
        $this->token = $token;
        $this->memory = new SharedMemoryClient($this->shared);
    }

    // @ description: A simple generator of random string for socket connection
    protected function generateRandomString(int $length = 10) : string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /*
    * @ description: Manage events by using this listener.
    * @ param: The name of the event, it must be in the class::$events array
    * @ param: The function for this event. The only arg passed is an object (or stdClass) with all informations
    */
    public function on(string $event, callable $callback) : void {
        if (!in_array($event, $this->events)) {
            return;
        }
        // Now let's start the event handler
        //$this->callbacks->{$event} = $callback;
        
        $this->callbacks->$event = function(Core $core, $args) use ($callback) {
            $callback($core, $args);
        };
    }

    // @ description: Connect to the websocket server
    public function connect() : void {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!@socket_connect($this->socket, $this->host, $this->port)) {
            return;
        }
        $this->client = new SocketClient($this->socket, $this->memory);
        if ($this->handshake()) {
            // CONNECTED
            $this->pid = $this->client->enableHeartbeat();
            // $this->callbacks->ready($this);
            $this->loop();
        } else {
            return;
        }
    }

    // @ description: Read the server info from shared memory
    public function getServer() : object|null {
        return @json_decode($this->memory->read());
    }

    // @ description: The interal loop, this thing can work async if defined in class::$async
    protected function loop() : void {
        while ($this->execution) {
            // Let's listen to events
            $event = json_decode($this->client->read());
            if (!$event) {
                continue;
            }
            if (@$event->status === 201 && @$event->event && @$event->data->event) {
                // It's an event, let's see which event bruh
                // First we create the data object
                $container = new \stdClass;
                foreach ($event->data->data as $object => $value) {
                    if (@$value->type != null) {
                        if ($value->type == 'player') {
                            $container->{$object} = (new Player($value, $this->client));
                        } elseif ($value->type == 'block') {
                            $container->{$object} = (new Block($value, $this->client));
                        } elseif ($value->type == 'item') {
                            $container->{$object} = (new Item($value, $this->client));
                        } elseif ($value->type == 'entity') {
                            $container->{$object} = (new Entity($value, $this->client));
                        } else {
                            $container->{$object} = $value->type;
                        }
                    }
                }
                $event = $event->data->eventName;
                $tds = $this->callbacks;
                //var_dump($this->callbacks);
                if (@$this->callbacks->$event !== null) {
                    $tds = $this->callbacks;
                    ($tds->$event)($this, $container);
                }
            }
        }
    }

    // @ description: Terminate the current process and childs if presents
    public function exit() : void {
        $this->execution = false;
        foreach ($this->childs as $child) {
            echo "kill -9 {$child}";
        }
        echo "kill -9 {$this->pid}";
        $this->client->close();
    }

    // @ description: Do an handshake to connect to the websocket server
    protected function handshake() : bool{
        $key = $this->generateRandomString();
        $token = base64_encode($this->token);
        $headers = "GET / HTTP/1.1\r\n";
        $headers .= "Type: simple-connection\r\n";
        $headers .= "Host: {$this->host}\r\n";
        $headers .= "Connection: Upgrade\r\n";
        $headers .= "Upgrade: websocket\r\n";
        $headers .= "Sec-WebSocket-Key: {$key}\r\n";
        $headers .= "Sec-WebSocket-Version: 13\r\n";
        $headers .= "Sec-WebSocket-Extensions: permessage-deflate; client_max_window_bits\r\n";
        $headers .= "Authorization: Basic {$token}\r\n";
        $this->client->send($headers);
        //$this->client->read();
        $read = '{' . @explode("{", $this->client->read())[1];
        $response = @json_decode($read);
        if ($response !== false && @$response->connected) {
            $this->client->id = $response->id;
            return true;
        }
        return false;
    }
}