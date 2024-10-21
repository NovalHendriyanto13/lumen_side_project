<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    // Specify the table associated with the model
    protected $table = 'pengaduan';

    // Specify the primary key if different from the default 'id'
    protected $primaryKey = 'id';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'no_pengaduan',
        'tgl_pengaduan',
        'deskripsi',
        'tgl_selesai',
        'foto_pengaduan',
        'status',
    ];

    // Specify any attributes that should be cast to native types
    protected $casts = [
        'tgl_pengaduan' => 'date',
        'tgl_selesai' => 'date',
    ];

    // Define relationships if needed (e.g., belongsTo, hasMany, etc.)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function requestDetails()
    // {
    //     return $this->hasMany(RequestDetail::class);
    // }

    public function getTglPengaduanAttribute($value) {
        return date('Y-m-d', strtotime($value));
    }

    public function getTglSelesaiAttribute($value) {
        return date('Y-m-d', strtotime($value));
    }
}
