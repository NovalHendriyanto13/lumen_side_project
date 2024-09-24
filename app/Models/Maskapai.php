<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maskapai extends Model
{
    // Define the table associated with the model
    protected $table = 'maskapai';

    // Specify the primary key if different from the default 'id'
    protected $primaryKey = 'id';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'code',
        'nama',
    ];
}
