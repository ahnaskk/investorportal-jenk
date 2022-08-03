<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvestorHelper;
class LiquidtyUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $investor_ids;
    public $description;
    public $merchant_id;
    public $liquidity_adjuster;
    public function __construct($investor_ids,$description,$merchant_id,$liquidity_adjuster='') {
        $this->investor_ids       = $investor_ids;
        $this->description        = $description;
        $this->merchant_id        = $merchant_id;
        $this->liquidity_adjuster = $liquidity_adjuster;
    }
    public function handle() {
        InvestorHelper::update_liquidity($this->investor_ids, $this->description, $this->merchant_id, $this->liquidity_adjuster);
    }
}
