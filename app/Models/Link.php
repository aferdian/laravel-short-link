<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_url',
        'short_code',
        'alias',
        'name',
        'description',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
