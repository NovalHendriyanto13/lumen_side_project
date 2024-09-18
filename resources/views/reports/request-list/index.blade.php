<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        .header {
            text-align: center;
        }
        table {
            width: 100%;
            border-spacing: 0px;
        }
        thead {
            text-align: center;
            background-color: lightgray;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>{{ $title }}</h3>
        <div>{{ $crew }} </div>
    </div>
    <div class="content">
        <table border="1">
            <thead>
            <tr>
                <td rowspan="3">Date</td>
                <td rowspan="3">Guest Name</td>
                <td rowspan="3">Room</td>
                <td colspan="3">Pickup</td>
                <td colspan="2">Total</td>
                <td rowspan="3">Date</td>
                <td colspan="3">Delivery</td>
                <td colspan="2">Total</td>
            </tr>
            <tr>
                <td>Checked By</td>
                <td>Informasi Awal dari OT</td>
                <td>Pengambilan Tiba di kamar</td>
                <td rowspan="2">LD</td>
                <td rowspan="2">Keterangan</td>
                <td>Checked By</td>
                <td>Informasi Awal dari OT</td>
                <td>Pengambilan Tiba di kamar</td>
                <td rowspan="2">HR</td>
                <td rowspan="2">Box</td>
            </tr>
            <tr>
                <td>Name</td>
                <td>Time</td>
                <td>Time</td>
                <td>Name</td>
                <td>Time</td>
                <td>Time</td>
            </tr>
            
            </thead>
            <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->tgl_permintaan }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->no_kamar }}</td>
                <td>{{ $item->checked_by }}</td>
                <td></td>
                <td>{{ $item->jam_pickup }}</td>
                <td></td>
                <td>{{ $item->nama_pakaian}} - {{ $item->deskripsi }}</td>
                <td>{{ $item->tgl_selesai }}</td>
                <td>{{ $item->delivery_by }}</td>
                <td></td>
                <td>{{ $item->jam_selesai }}</td>
                <td></td>
                <td>{{ $item->jml_item }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
