<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SearchHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'query_term',
        'location_name',
        'weather_data',
    ];

    protected $casts = [
        'weather_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
