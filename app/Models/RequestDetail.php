<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDetail extends Model
{
    // Define the table associated with the model
    protected $table = 'request_detail';

    // Specify the primary key, if not 'id'
    protected $primaryKey = 'id';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'request_list_id',
        'id_item',
        'jml_item',
        'description',
        'image',
    ];

    // Define relationships, if necessary
    public function requestList()
    {
        return $this->belongsTo(RequestList::class);
    }
}
