<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'target_percentage',
        'user_id'
    ];

    protected $casts = [
	'target_percentage' => 'int',
    ];
    
    public function user() {
	return $this->belongsTo(User::class);
    }
    
    public function timeBlocks() {
	return $this->hasMany(TimeBlock::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }    
}
