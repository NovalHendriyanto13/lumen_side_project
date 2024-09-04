<?php

namespace App\Exports;

use App\Models\RequestList;
use App\Models\RequestDetail;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RequestListExport implements FromCollection, WithHeadings
{
    protected $_data;

    public function __construct(Collection $data)
    {
        $this->_data = $data;
    }

    public function collection()
    {
        return $this->_data;
    }

    public function map($data): array
    {
        return [
            $data->tgl_permintaan,
            $data->tgl_selesai,
            $data->nama,
            $data->no_permintaan,
            $data->no_pickup,
            $data->no_kamar,
            $data->status,
            $data->kode_pakaian,
            $data->nama_pakaian,
            $data->jml_item,
            $data->deskripsi,
        ];
    }

    public function headings(): array
    {
        return [
            'Tanggal Permintaan',
            'Tanggal Selesai', 
            'Nama', 
            'No Permintaan', 
            'No Pickup',
            'No Kamar',
            'Status',
            'Kode Pakaian',
            'Nama Pakaian',
            'Jumlah',
            'Deskripsi'
        ];
    }

}
