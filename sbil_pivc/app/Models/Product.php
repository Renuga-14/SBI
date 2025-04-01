<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = ['uin_no', 'product_name', 'source_id', 'product_slug', 'status', 'created_on', 'updated_on'];

    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * Validate UIN Number
     */
    public static function validateUin($uinNo, $srcId)
    {
        return self::whereRaw('LOWER(uin_no) = ?', [strtolower($uinNo)])
                    ->where('source_id', $srcId)
                    ->where('status', 1)
                    ->select('id', 'uin_no', 'source_id', 'product_name', 'product_slug', 'status')
                    ->first();
    }


}
