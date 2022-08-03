<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    protected $hidden = ['pivot', 'created_at', 'updated_at'];

    public function firewallips()
    {
        return $this->hasMany(FirewallUser::class, 'firewall_id');
    }
}
