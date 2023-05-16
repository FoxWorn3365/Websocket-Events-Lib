<?php

namespace FoxWorn3365\SocketManager;

class SocketClient {
    // @ description: The protected stream resource
    protected \Socket $stream;
    // @ description: Whether or not the client is connected
    public bool $connected = false;
    // @ description: The client (almost) uniquie ID
    // @ note: This is assigned by the server when the client connects
    public int $id;
    // @ description: The time (in seconds) between every heartbeat (Hello world!)
    public int $heartbeat = 12;
    // @ description: The id of the shared memory block for the server data
    // @ note: Passed by reference, here it's useless because we've also the shared memory client
    public int $shared;
    // @ description: The shared memory client
    protected SharedMemoryClient $memory;

    /*
    * @ description: The constructor
    * @ param: The connection, this must be a "resource" but i can't force this!
    */
    function __construct(\Socket $connection, SharedMemoryClient $memory) {
        //$this->id = rand(10, 1000) . rand(10, 1000);
        $this->stream = $connection;
        $this->connected = true;
        $this->memory = $memory;
    }

    // @ description: Read a message from the socket stream
    public function read(int $lenght = 10024) : string {
        return socket_read($this->stream, $lenght);
    }

    // @ description: Write a message to the socket stream
    // @ param: The message
    public function write(string $message) : bool {
        if (!$this->connected) {
            return false;
        }
        if (!socket_write($this->stream, $message, strlen($message))) {
            return false;
        }
        return true;
    }

    // @ description: Write a message to the socket stream
    // @ param: The message
    // @ note: Alias of class::write
    public function send(string $message) : bool {
        return $this->write($message);
    }

    // @ description: Close the socket stream
    public function close() : void {
        $this->connected = false;
        socket_close($this->stream);
    }

    // @ description: Send an heartbeat every class::$heartbeat seconds
    // @ return: The child PID
    public function enableHeartbeat() : ?int {
        $pid = pcntl_fork();
        if ($pid == -1) {
            return null;
        } elseif ($pid) {
            return $pid;
        } else {
            while (true) {
                $this->send('heartbeat+hello world+server_chunk');
                echo "HEARTBEAT";
                $msg = $this->read();
                if (strlen($msg) > 10) {
                    $msg = json_decode($msg);
                    var_dump($msg);
                    echo "HEARTBEAT";
                    echo "\n\n";
                    //var_dump($msg);
                    $this->memory->write(json_encode($msg->data));
                }
                sleep($this->heartbeat);
            }
        }
        return $pid;
    }
}