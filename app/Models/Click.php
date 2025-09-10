<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    use HasFactory;

    public $timestamps = ["created_at"];
    const UPDATED_AT = null;

    protected $fillable = [
        'link_id',
        'ip_address',
        'browser',
        'os',
        'location',
    ];

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
