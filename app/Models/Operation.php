<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'actor_type',
        'name',
        'uuid',
        'started_at',
        'ended_at',
        'status'
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'id');
    }
}
