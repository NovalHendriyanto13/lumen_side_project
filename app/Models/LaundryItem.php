<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaundryItem extends Model
{
    // Define the table associated with the model
    protected $table = 'laundry_item';

    // Specify the primary key if different from the default 'id'
    protected $primaryKey = 'id';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'id_item',
        'nama',
    ];
}
