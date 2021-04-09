<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeBlock extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'block_date',
        'block_length',
        'description',
        'category_id',
        'user_id'
    ];
    
    protected $casts = [
        'block_length' => 'int'
    ];

    public function category() {
	return $this->belongsTo(Category::class);
    }

    public function user() {
	return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
