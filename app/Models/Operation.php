<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'integration_id',
        'actor_id',
        'actor_type',
        'name',
        'status',
        'started_at',
        'ended_at'
    ];

    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
