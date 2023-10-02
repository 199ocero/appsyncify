<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppCombination extends Model
{
    use HasFactory;

    protected $table = 'app_combination';

    protected $fillable = [
        'first_app_id',
        'second_app_id',
        'is_active',
        'features'
    ];

    protected $casts = [
        'features' => 'array'
    ];

    public function firstApp()
    {
        return $this->belongsTo(App::class, 'first_app_id', 'id');
    }

    public function secondApp()
    {
        return $this->belongsTo(App::class, 'second_app_id', 'id');
    }
}
