<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'owner_type',
        'owner_id',
        'file_url',
        'file_type',
        'created_at',
    ];

    public $timestamps = false;
}

