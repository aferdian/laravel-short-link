<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Storage;

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

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($link) {
            if ($link->image) {
                // Remove '/assets/' prefix to get the path relative to the public disk root
                $path = str_replace('/assets/', '', $link->image);
                Storage::disk('public')->delete($path);
            }
        });
    }

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
