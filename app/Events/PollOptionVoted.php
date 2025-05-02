<?php

namespace App\Events;

use App\Models\PollOption;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PollOptionVoted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $option_id;
    public $votes;

    /**
     * Create a new event instance.
     * 
     * @param PollOption $pollOption
     * @return void
     * 
     * This constructor initializes the event with the given PollOption instance.
     * It sets the option_id and votes properties based on the PollOption instance.
     * The option_id is the ID of the poll option that was voted for,
     * and votes is the number of votes that option has received.
     */
    public function __construct(PollOption $pollOption)
    {
        $this->option_id = $pollOption->id;
        $this->votes = $pollOption->votes;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('polls'),
        ];
    }
}
