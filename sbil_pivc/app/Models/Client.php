<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'ckey',
        'short_name',
        'full_name',
        'description',
        'status',
    ];

    public $timestamps = false; // Since we use custom timestamps `created_on` and `updated_on`
}
