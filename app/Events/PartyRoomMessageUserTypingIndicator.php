<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartyRoomMessageUserTypingIndicator implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    /**
     * The name of the queue on which to place the event.
     *
     * @var string
     */
    public $broadcastQueue = 'socket-broadcast';


    /**
     * Broadcast data
     *
     * @var object
     */
    private $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        // hide some unnecessary information
        $this->message = $message;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender_name,
            'party_id' => $this->message->party_id,
            'room_id' => $this->message->room_id,
            'is_typing' => $this->message->is_typing,
            'created_at' => $this->message->created_at->format('Y-m-d H:i:s'),
            'created_at_timestamp' => $this->message->created_at,
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'party.room.message.user.typing.indicator';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel("Party.{$this->message->party_id}.Room.{$this->message->room_id}");
    }
}
