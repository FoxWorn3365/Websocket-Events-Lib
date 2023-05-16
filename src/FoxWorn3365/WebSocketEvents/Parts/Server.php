<?php
namespace FoxWorn3365\WebSocketEvents\Parts;

use FoxWorn3365\SocketManager\SocketClient;

class Server {
    // @ description: All fields that can be filled
    protected array $fillable = [
        'data_path',
        'difficulty',
        'file_path',
        'force_gamemode',
        'ip',
        'ip_bans',
        'ipv6',
        'max_players',
        'motd',
        'name',
        'ops',
        'online_mode',
        'pocketmine_version',
        'port',
        'tick',
        'tps',
        'version',
        'hardcore'
    ];
    // @ description: The socket client
    protected SocketClient $socket;

    /*
    * @ description: Create this class from an object
    * @ param: The parent object
    * @ param: The socket client
    */
    function __construct(object $parent, SocketClient $socket) {
        $this->socket = $socket;
        foreach ($parent as $key => $value) {
            $this->{$key} = $value;
        }

        foreach ($this->aliases as $final => $from) {
            $this->{$final} = $this->{$from};
        }
    }

    // Player utils function, fetch is included because it's a function
    /*
    * @ description: Execute a specific action (actually a command) from the console
    * @ param: The action (capitalCase because it's a function)
    * @ param: Max two args in an array
    * @ note: For now, execution can't be async also using pcntl!
    */
    private function execute(string $action) : bool {
        $this->socket->send(json_encode([
            'action' => 'EXECUTE',
            'fetch' => 'server',
            'target' => null,
            'action' => $action,
            'args' => null
        ]));
        if (@json_decode($this->socket->read())->status === 200) {
            return true;
        }
        return false;
    }

    // @ description: Shutdown the server
    public function shutdown() : bool {
        return $this->execute('stop');
    }

    // @ description: Send a command from the console
    // @ param: The command
    public function sendCommand(string $command) : bool {
        return $this->execute($command);
    }
}