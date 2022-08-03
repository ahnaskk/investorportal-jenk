<?php

namespace App\Events;

//use App\MerchantPayments;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class PaymentDeleted extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct($id)
    {
        $this->partPaymentId = $id;
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
