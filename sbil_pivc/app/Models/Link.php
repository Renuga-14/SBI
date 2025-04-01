<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table="links";
    protected $fillable = ['proposal_no', 'product_id', 'version']; // Define fillable attributes


     // Define Relationship with Product Model
     public function product()
     {
         return $this->belongsTo(Product::class, 'product_id');
     }
}
