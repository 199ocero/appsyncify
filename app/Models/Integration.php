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
        'is_active',
        'user_id',
        'app_combination_id',
        'first_app_token_id',
        'second_app_token_id',
        'first_app_settings',
        'second_app_settings',
        'custom_field_mapping',
        'step'
    ];

    protected $casts = [
        'custom_field_mapping' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appCombination()
    {
        return $this->belongsTo(AppCombination::class);
    }

    public function firstAppToken()
    {
        return $this->belongsTo(Token::class, 'first_app_token_id');
    }

    public function secondAppToken()
    {
        return $this->belongsTo(Token::class, 'second_app_token_id');
    }
}
