<?php

namespace App\Exports;

use App\Models\Pengaduan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class PengaduanExport implements FromCollection, WithHeadings, WithEvents
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
            $data->jam_pickup,
            $data->jam_selesai,
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
            'Jam Pickup',
            'Jam Selesai',
            'Kode Pakaian',
            'Nama Pakaian',
            'Jumlah',
            'Deskripsi'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge cells, for example, merging A1:E1
                $event->sheet->getDelegate()->mergeCells('A1:E1');

                // Optionally, apply styling
                $event->sheet->getDelegate()->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
            },
        ];
    }

}
