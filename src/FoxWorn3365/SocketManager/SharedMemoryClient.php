<?php

namespace FoxWorn3365\SocketManager;

class SharedMemoryClient {
    // @ description: The ID of the shared memory block
    public int $id;
    // @ description: The original shmop client
    protected \Shmop $memory;

    /*
    * @ description: The class constructor. Here you can start a new shmop istance by id or random id!
    * @ param: The ID of the shared memory segment, can be null
    */
    function __construct(int $id = null) {
        if ($id === null) {
            $id = rand(10, 1000) . rand(5, 1000);
        }
        $this->memory = shmop_open($id, "c", 0644, 2048);
        $this->id = $id;
    }

    // @ description: Read a shmop shared memory segment
    public function read() : string {
        return shmop_read($this->memory, 0, 0);
    }

    // @ description: Write a shmop shared memory segment
    // @ param: The message
    public function write(string $message) : bool {
        if (@shmop_write($this->memory, $message, 0) !== strlen($message)) {
            return false;
        }
        return true;
    }
    
    // @ description: Get the size of a shmop shared memory segment
    public function size() : int {
        return @shmop_size($this->memory);
    }
}