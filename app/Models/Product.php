<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'zoho_id as item_id', 'name', 'description', 'rate', 'available_quantity', 'vendor_id'
    ];

    protected $appends = ['item_id'];

    public function getItemIdAttribute()
    {
        return $this->attributes['zoho_id'];
    }

}
