<?php
namespace FoxWorn3365\WebSocketEvents\Parts;

use FoxWorn3365\SocketManager\SocketClient;

class Player {
    // @ description: All fields that can be filled
    protected array $fillable = [
        'name',
        'online',
        'display_name',
        'name_tag',
        'gamemode',
        'healt',
        'id',
        'last_played',
        'location',
        'position',
        'spawn',
        'uuid',
        'viewers',
        'world',
        'skin'
    ];
    // @ description: Aliases of fields
    protected array $aliases = [
        'username' => 'name',
        'nickname' => 'display_name'
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
    * @ description: Execute a specific action (actually a PocketMine\Player function) for this player
    * @ param: The action (capitalCase because it's a function)
    * @ param: Max two args in an array
    * @ note: For now, execution can't be async also using pcntl!
    */
    private function execute(string $action, array $args = []) : bool {
        $this->socket->send(json_encode([
            'action' => 'EXECUTE',
            'fetch' => 'player',
            'target' => $this->username,
            'action' => $action,
            'args' => $args
        ]));
        if (@json_decode($this->socket->read())->status === 200) {
            return true;
        }
        return false;
    }

    /*
    * @ description: Send a message in the chat by the player
    * @ param: The message
    */
    public function chat(string $message) : bool {
        return $this->execute('chat', [$message]);
    }

    // @ description: Damage the player's armor
    // @ param: Damage value
    public function damageArmor(float $damage) : bool {
        return $this->execute('damageArmor', [$damage]);
    }

    // @ description: Disconnect (not kick) the player with a reason
    public function disconnect(string $reason) : bool {
        return $this->execute('disconnect', [$reason]);
    }

    // @ description: Stop a player from burning
    public function extinguish() : bool {
        return $this->execute('extinguish');
    }

    // @ description: Add heal to the player
    public function heal(float $amount) : bool {
        return $this->setHealt($this->healt + $amount);
    }

    // @ description: Make the player jump
    public function jump() : bool {
        return $this->execute('jump');
    }

    // @ description: Kick a player with a reason
    public function kick(string $reason) : bool {
        return $this->execute('kick', [$reason]);
    }

    // @ description: Kill a player
    public function kill() : bool {
        return $this->execute('kill');
    }

    // @ description: Apply knockback effect to the user.
    // @ note: The power will be 0.4
    public function knockBack(float $x, float $y) : bool {
        return $this->execute('knockBack', [$x, $y]);
    }

    // @ description: Close the current window
    public function removeCurrentWindows() : bool {
        return $this->execute('removeCurrentWindows');
    }

    // @ description: Remove a title shown to the player
    public function removeTitles() : bool {
        return $this->execute('removeTitles');
    }

    // @ description: $eset the fall distance
    public function resetFallDistance() : bool {
        return $this->execute('resetFallDistance');
    }

    // @ description: Reset the shown title to the player
    public function resetTitles() : bool {
        return $this->execute('resetTitles');
    }

    // @ description: Respawn the player
    public function respawn() : bool {
        return $this->execute('respawn');
    }

    // @ description: Send a message to the player
    public function sendMessage(string $message) : bool {
        return $this->execute('sendMessage', [$message]);
    }

    // @ description: Send a popup to the player
    public function sendPopup(string $message) : bool {
        return $this->execute('sendPopup', [$message]);
    }

    // @ description: Send a subtitle to the player
    public function sendSubTitle(string $message) : bool {
        return $this->execute('sendSubTitle', [$message]);
    }

    // @ description: Send a tip to the player
    public function sendTip(string $message) : bool {
        return $this->execute('sendTip', [$message]);
    }

    // @ description: Send a title to the player
    public function sendTitle(string $message) : bool {
        return $this->execute('sendTitle', [$message]);
    }

    // @ description: Send a toast notification to the player
    public function sendToastNotification(string $title, string $message) : bool {
        return $this->execute('sendToastNotification', [$title, $message]);
    }

    // @ description: Absorp some damage
    public function setAbsorption(float $absorption) : bool {
        return $this->execute('setAbsorption', [$absorption]);
    }

    // @ description: Set the air supply when the player is underwather
    public function setAirSupplyTicks(int $ticks) : bool {
        return $this->execute('setAirSupplyTicks', [$ticks]);
    }

    // @ description: Allows flight
    public function setAllowFlight(bool $value) : bool {
        return $this->execute('setAllowFlight', [$value]);
    }

    // @ description: Set auto jump for the player
    public function setAutoJump(bool $value) : bool {
        return $this->execute('setAAutoJump', [$value]);
    }

    // @ description: Set breathing for the player
    public function setBreathing(bool $value) : bool {
        return $this->execute('setBreathing', [$value]);
    }

    // @ description: Can the player climb?
    public function setCanClimb(bool $value) : bool {
        return $this->execute('setCanClimb', [$value]);
    }

    // @ description: Can the player clib walls?
    public function setCanClimbWalls(bool $value) : bool {
        return $this->execute('setCanClimbWalls', [$value]);
    }

    // @ description: Update the display name
    public function setDisplayName(string $name) : bool {
        return $this->execute('setDisplayName', [$name]);
    }

    // @ description: Set the fall distance
    public function setFallDistance(float $fallDistance) : bool {
        return $this->execute('setFallDistance', [$fallDistance]);
    }

    // @ description: Set the fire ticks
    public function setFireTicks(int $ticks) : bool {
        return $this->execute('setFireTicks', [$ticks]);
    }

    // @ description: Can the player fly?
    public function setFlying(bool $value) : bool {
        return $this->execute('setFlying', [$value]);
    }

    // @ description: Update player's gamemode
    public function setGamemode(int $gamemode) : bool {
        return $this->execute('setGamemode', [$gamemode]);
    }

    // @ description: Wheather or not the player have a gravity
    public function setGravity(float $gravity) : bool {
        return $this->execute('setGravity', [$gravity]);
    }

    // @ description: Wheather or not the player have a gravity
    public function setHasGravity(bool $value = true) : bool {
        return $this->execute('setHasGravity', [$value]);
    }

    // @ description: Set the player's healt
    public function setHealt(float $amount) : bool {
        return $this->execute('setHealt', [$amount]);
    }

    // @ description: Can the player move?
    public function setImmobile(bool $value) : bool {
        return $this->execute('setImmobile', [$amount]);
    }

    // @ description: Can the player be seen?
    public function setInvisible(bool $value) : bool {
        return $this->execute('setInvisible', [$value]);
    }

    // @ description: Underwather things
    public function setMaxAirSupplyTicks(int $ticks) : bool {
        return $this->execute('setMaxAirSupplyTicks', [$ticks]);
    }

    // @ description: Set the player max healt
    public function setMaxHealth(int $max) : bool {
        return $this->execute('setMaxHealth', [$max]);
    }

    // @ description: Set the player speed
    public function setSpeed(float $speed) : bool {
        return $this->execute('setMovementSpeed', [$speed]);
    }

    // @ description: Update the player nametag
    public function setNameTag(string $name) : bool {
        return $this->execute('setNameTag', [$name]);
    }

    // @ description: Is the nametag always visible?
    public function setNameTagAlwaysVisible(bool $value) : bool {
        return $this->execute('setNameTagAlwaysVisible', [$value]);
    }

    // @ description: Is the nametag visible?
    public function setNameTagVisible(bool $value) : bool {
        return $this->execute('setNameTagVisible', [$value]);
    }

    // @ description: Is the player on fire?
    public function setOnFire(int $seconds) : bool {
        return $this->execute('setOnFire', [$seconds]);
    }

    // @ description: Set the player scale
    public function setScale(float $value) : bool {
        return $this->execute('setScale', [$value]);
    }

    // @ description: Idk
    public function setScoreTag(string $score) : bool {
        return $this->execute('setScoreTag', [$score]);
    }

    // @ description: Can the player make noise?
    public function setSilent(bool $value) : bool {
        return $this->execute('setSilent', [$value]);
    }

    // @ description: Is the player seaking?
    public function setSneaking(bool $value) : bool {
        return $this->execute('setSneaking', [$value]);
    }

    // @ description: Is the player sprinting?
    public function setSprinting(bool $value) : bool {
        return $this->execute('setSprinting', [$value]);
    }

    // @ description: Is the player swimming?
    public function setSwimming(bool $value) : bool {
        return $this->execute('setSwimming', [$value]);
    }

    // @ description: ---
    public function setUsingItem(bool $value) : bool {
        return $this->execute('setUsingItem', [$value]);
    }

    // @ description: Set the player view distance
    public function setViewDistance(int $value) : bool {
        return $this->execute('setViewDistance', [$value]);
    }

    // @ description: Stop sleep
    public function stopSleep() : bool {
        return $this->execute('stopSleep');
    }

    // @ description: Use the hold item
    public function useHeldItem() : bool {
        return $this->execute('useHeldItem');
    }
}