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
    </div>
    <div class="content">
        <div>Tanggal : {{ date('d F Y') }}</div>
        <table border="1">
            <thead>
            <tr>
                <td>Complain Date</td>
                <td>Finished Date</td>
                <td>NIK</td>
                <td>Name</td>
                <td>Complain No</td>
                <td>Complain</td>
                <td>Response No</td>
                <td>Response</td>
                <td>Status</td>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->tgl_pengaduan }}</td>
                <td>{{ $item->tgl_selesai }}</td>
                <td>{{ $item->nik }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->no_pengaduan }}</td>
                <td>{{ $item->pengaduan}}</td>
                <td>{{ $item->no_tanggapan }}</td>
                <td>{{ $item->tanggapan }}</td>
                <td>{{ $item->status }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
