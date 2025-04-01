<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sources extends Model
{
    use HasFactory;
    protected $table = 'sources';

    protected $fillable = ['name', 'desc', 'status', 'created_on', 'updated_on'];

    public $timestamps = false;

    /**
     * Validate Source
     */
    public static function validateSource($source)
    {
        return self::whereRaw('LOWER(name) = ?', [strtolower($source)])
            ->where('status', 1)
            ->first(); // Returns the first matching record or null
    }
}
