<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Respond extends Model
{
    // Define the table associated with the model
    protected $table = 'tanggapan';

    // Specify the primary key, if not 'id'
    protected $primaryKey = 'id';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
        'pengaduan_id',
        'user_id',
        'no_tanggapan',
        'tgl_tanggapan',
        'foto_tanggapan',
        'deskripsi',
        'status'
    ];

    // Define relationships, if necessary
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
