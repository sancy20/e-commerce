<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeValueRequest extends Model
{
    protected $fillable = ['vendor_id', 'attribute_id', 'value', 'status', 'admin_notes'];
}
