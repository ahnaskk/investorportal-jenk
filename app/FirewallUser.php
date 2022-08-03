<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FirewallUser extends Model
{
    protected $table = 'firewall_user';

    protected $fillable = ['firewall_id', 'role_id'];

    public function firewallroles()
    {
        return $this->hasOne(Firewall::class, 'id');
    }
}
