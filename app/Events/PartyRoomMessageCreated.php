<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartyRoomMessageCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
    public function __construct(\App\Message $message)
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
            'party_id' => $this->message->party_id,
            'room_id' => $this->message->room_id,
            'content' => $this->message->content,
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
        return 'party.room.message.created';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel("Party.{$this->message->party_id}.Room.{$this->message->room_id}");
    }
}
