<?php
namespace FoxWorn3365\WebSocketEvents\Parts;

use FoxWorn3365\SocketManager\SocketClient;

class Item {
    // @ description: All fields that can be filled
    protected array $fillable = [
        'count',
        'custom_name',
        'id',
        'max_stack_size',
        'name',
        'name_tag',
        'vanilla_name',
        'null'
    ];
    // @ description: The block name
    public string $name;
    // @ description: The socket client
    protected SocketClient $socket;

    /*
    * @ description: The constructor class. You can use it also out of the code
    * @ param: The parent, must be an object or stdClass
    * @ param: The original socket client
    * @ note: This class now is kinda useless because the server can't make action to items. Maybe in the future...
    */
    function __construct(object $parent, SocketClient $client) {
        $this->socket = $client;
        foreach ($parent as $key => $value) {
            $this->{$key} = $value;
        }
    }
}