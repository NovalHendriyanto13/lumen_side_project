<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestList extends Model
{
    // Specify the table associated with the model
    protected $table = 'request_list';

    // Specify the primary key if different from the default 'id'
    protected $primaryKey = 'id';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'no_permintaan',
        'no_pickup',
        'tgl_permintaan',
        'no_kamar',
        'tgl_selesai',
        'status',
    ];

    // Specify any attributes that should be cast to native types
    protected $casts = [
        'tgl_permintaan' => 'date',
        'tgl_selesai' => 'date',
    ];

    // Define relationships if needed (e.g., belongsTo, hasMany, etc.)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function requestDetails()
    {
        return $this->hasMany(RequestDetail::class);
    }
}
