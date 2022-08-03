<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvestorDocuments extends Model
{
    protected $fillable = ['global_status', 'document_type_id', 'id', 'merchant_id', 'investor_id', 'title', 'file_name', 'admin_id', 'status', 'created_at', 'updated_at'];
}
