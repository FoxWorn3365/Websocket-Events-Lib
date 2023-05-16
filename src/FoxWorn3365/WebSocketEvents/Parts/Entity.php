<?php
namespace FoxWorn3365\WebSocketEvents\Parts;

use FoxWorn3365\SocketManager\SocketClient;

class Entity {
    // @ description: All fields that can be filled
    protected array $fillable = [
        'name',
        'healt',
        'location',
        'max_healt',
        'world',
        'xp',
        'viewers',
        'id'
    ];
    // @ description: The user name
    public string $username;
    // @ description: The user UUID
    public string $uuid;
    // @ description: The socket client
    protected SocketClient $socket;

    /*
    * @ description: Create this class from an object
    * @ param: The parent object
    * @ param: The socket client
    * @ note: Actually this class is kinda useless because of the plugin: it can't manage actions with entities, blocks and items: only players and server
    */
    function __construct(object $parent, SocketClient $socket) {
        $this->socket = $socket;
        foreach ($parent as $key => $value) {
            $this->{$key} = $value;
        }
    }
}