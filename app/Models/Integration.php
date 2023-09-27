<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'first_app_id',
        'second_app_id',
        'user_id',
    ];

    public function firstApp()
    {
        return $this->belongsTo(App::class, 'first_app_id');
    }

    public function secondApp()
    {
        return $this->belongsTo(App::class, 'second_app_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
