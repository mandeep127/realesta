<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class property extends Model
{
    use HasFactory;
    protected $fillable = [
        'price',
        'bedrooms',
        'bathrooms',
        'size',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'status',
        'image',
        'created_by',
    ];
}
