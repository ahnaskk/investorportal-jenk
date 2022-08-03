<?php

namespace App\Events;

use Carbon\Carbon;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PaymentGenerated extends Event implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @param Carbon $date
     */
    public function __construct(Carbon $date, $merchant_id = 0)
    {
        $this->date = $date;
        $this->merchant_id = $merchant_id;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
