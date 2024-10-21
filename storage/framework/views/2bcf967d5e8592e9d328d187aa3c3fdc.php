<!DOCTYPE html>
<html>
<head>
    <title><?php echo e($title); ?></title>
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
        <h3><?php echo e($title); ?></h3>
        <div><?php echo e($crew); ?> </div>
    </div>
    <div class="content">
        <div>Tanggal : <?php echo e(date('d F Y')); ?></div>
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
            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($item->tgl_permintaan); ?></td>
                <td><?php echo e($item->nama); ?></td>
                <td><?php echo e($item->no_kamar); ?></td>
                <td><?php echo e($item->checked_by); ?></td>
                <td></td>
                <td><?php echo e($item->jam_pickup); ?></td>
                <td></td>
                <td><?php echo e($item->nama_pakaian); ?> - <?php echo e($item->deskripsi); ?></td>
                <td><?php echo e($item->tgl_selesai); ?></td>
                <td><?php echo e($item->delivery_by); ?></td>
                <td></td>
                <td><?php echo e($item->jam_selesai); ?></td>
                <td></td>
                <td><?php echo e($item->jml_item); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php /**PATH /var/www/html/OTHERS/side/laundry/BE/resources/views/reports/request-list/index.blade.php ENDPATH**/ ?>