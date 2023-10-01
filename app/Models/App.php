<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'app_code',
        'is_active',
    ];

    public function combinations()
    {
        return $this->belongsToMany(App::class, 'app_combination', 'first_app_id', 'second_app_id');
    }
}
