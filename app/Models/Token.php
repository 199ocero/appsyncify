<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_id',
        'token'
    ];

    public function app()
    {
        return $this->belongsTo(App::class);
    }
}
