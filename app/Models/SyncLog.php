<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'operation_id',
        'log_type',
        'message',
        'api_endpoint',
        'request_data',
        'response_data'
    ];

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }
}
