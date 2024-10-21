<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\Tanggapan;
use App\Exports\RequestListExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PengaduanController extends Controller
{
    private $_statuses = ['new', 'progress', 'finished', 'pending', 'deleted'];
    // Retrieve all request lists
    public function index(Request $request)
    {
        $user = auth()->user();
        $role = $user->role;
                
        $requests = Pengaduan::select([
            'pengaduan.id',
            'pengaduan.user_id',
            'pengaduan.no_pengaduan',
            'pengaduan.tgl_pengaduan',
            'pengaduan.deskripsi',
            'pengaduan.foto_pengaduan',
            'pengaduan.status',
            'users.nik',
            'users.nama'
        ])
            ->when($role == 'user', function($q) use ($user) {
                return $q->where('user_id', $user->id);
            })
            ->when(!empty($request->status), function($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when((!empty($request->start_date) && !empty($request->end_date)), function($q) use ($request) {
                return $q->whereBetween('tgl_pengaduan', [date('Y-m-d', strtotime($request->start_date)), date('Y-m-d', strtotime($request->end_date))]);
            })
            ->join('users', 'pengaduan.user_id', 'users.id')
            ->get();

        $requests->map(function($item) {
            if (!empty($item->foto_pengaduan)) {
                $item->foto_pengaduan = env('APP_URL', ''). '/api/image?filename='.base64_encode($item->foto_pengaduan);
            }
            return $item;
        });
    

        return $this->success($requests);
    }

    // Retrieve a single request list by ID
    public function show($id)
    {
        $pengaduan = Pengaduan::select([
            'pengaduan.id',
            'pengaduan.user_id',
            'pengaduan.no_pengaduan',
            'pengaduan.tgl_pengaduan',
            'pengaduan.deskripsi',
            'pengaduan.foto_pengaduan',
            'pengaduan.status',
            'users.nik',
            'users.nama'
        ])
            ->join('users', 'pengaduan.user_id', 'users.id')
            // ->leftJoin('tanggapan', 'request_detail.id_item', 'laundry_item.id')
            ->where('pengaduan.id', $id)->get();

        if (!$pengaduan) {
            return $this->failed([], 'Request not found', 404);
        }

        $pengaduan->map(function($item) {
            if (!empty($item->foto_pengaduan)) {
                $item->foto_pengaduan = env('APP_URL', ''). '/api/image?filename='.base64_encode($item->foto_pengaduan);
            }
            return $item;
        });

        return $this->success($pengaduan);
    }

    // Create a new request list
    public function store(Request $request)
    {
        $this->validate($request, [
            'deskripsi' => 'required'
        ]);

        $noPengaduan = 'PA'.date('ym').substr(strtotime('now'), -3, 3);
        $image = null;
        if ($request->file('foto_pengaduan')) {
            $filename = $request->file('foto_pengaduan')->getClientOriginalName();
            $r = $request->file('foto_pengaduan')->move(storage_path('images/'.$noPengaduan), $filename);
            $image = 'images/'.$noPengaduan.'/'.$r->getBasename();
        }

        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
            'no_pengaduan' => $noPengaduan,
            'tgl_pengaduan' => date('Y-m-d'),
            'deskripsi' => $request->deskripsi,
            'foto_pengaduan' => $image,
            'status' => 'new'
        ]);
        
        $newRequest = Pengaduan::create($payload);
        if (!empty($newRequest->foto_pengaduan)) {
            $newRequest->foto_pengaduan = env('APP_URL', ''). '/api/image?filename='.base64_encode($newRequest->foto_pengaduan);
        }

        return $this->success($newRequest, 201);
    }

    // Update an existing request list by ID
    public function update(Request $request, $id)
    {
        $pengaduan = Pengaduan::find($id);

        if (!$pengaduan) {
            return $this->failed([], 'Request not found', 404);
        }

        if ($pengaduan->status != 'new') {
            return $this->failed([], 'Update data is failed! Status is not "NEW"', 404);
        }
        $this->validate($request, [
            'deskripsi' => 'required',
        ]);

        $image = null;
        if ($request->file('foto_pengaduan')) {
            $filename = $request->file('foto_pengaduan')->getClientOriginalName();
            $r = $request->file('foto_pengaduan')->move(storage_path('images/'.$noPengaduan), $filename);
            $image = 'images/'.$noPengaduan.'/'.$r->getBasename();
        }

        if (!empty($image)) {
            $pengaduan->foto_pengaduan = $image;
        }

        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
        ]);
        
        $pengaduan->update($payload);

        return $this->success($pengaduan);
    }

    // Delete an existing request list by ID
    public function destroy($id)
    {
        $pengaduan = Pengaduan::find($id);

        if (!$pengaduan) {
            return $this->failed([], 'Request not found', 404);
        }

        $pengaduan->update(['status' => 'deleted']);

        return $this->success([], 'Request deleted successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $pengaduan = Pengaduan::find($id);

        if (!$pengaduan) {
            return $this->failed([], 'Request not found', 404);
        }

        $currentStatus = $pengaduan->status;
        $newStatusIndex = (array_search($currentStatus, $this->_statuses)) + 1;
        
        if ($newStatusIndex >= (count($this->_statuses))) {
            return $this->failed([], 'laundry sudah selesai', 401);
        }
        if ($this->_statuses[$newStatusIndex] == 'pickup') {
            $pengaduan->no_pickup = 'PU'.substr(strtotime('now'), -3, 3);
            $pengaduan->jam_pickup = date('H:i:s');
        }
        if ($this->_statuses[$newStatusIndex] == 'checking') {
            $pengaduan->checked_by = auth()->user()->id;
        }
        if ($this->_statuses[$newStatusIndex] == 'delivery') {
            $pengaduan->delivery_by = auth()->user()->id;
        }
        $pengaduan->status = $this->_statuses[$newStatusIndex];
        $pengaduan->save();

        return $this->success($pengaduan);
    }

    public function downloadReport(Request $request) {
        $pengaduan = Pengaduan::select([
            'request_list.tgl_permintaan',
            'request_list.tgl_selesai',
            'users.nama',
            'maskapai.nama AS user_kru',
            'request_list.no_permintaan',
            'request_list.no_pickup',
            'request_list.no_kamar',
            'request_list.status',
            'request_list.jam_pickup',
            'request_list.jam_selesai',
            'laundry_item.id_item AS kode_pakaian',
            'laundry_item.nama AS nama_pakaian',
            'request_detail.jml_item',
            'request_detail.description AS deskripsi',
        ])
            ->leftJoin('request_detail', 'request_list.id', '=', 'request_detail.request_list_id')
            ->leftJoin('users', 'request_list.user_id', '=', 'users.id')
            ->leftJoin('laundry_item', 'laundry_item.id', '=', 'request_detail.id_item')
            ->leftJoin('maskapai', 'maskapai.id', '=', 'users.user_kru')
            ->when(!empty($request->status), function($q) use ($request) {
                return $q->where('request_list.status', $request->status);
            })
            ->when(!empty($request->user_kru), function($q) use ($request) {
                return $q->where('users.user_kru', $request->user_kru);
            })
            ->orderBy('request_list.id', 'desc')
            ->get();

        $exports = new PengaduanExport($pengaduan);

        return Excel::download($exports, 'report.xlsx');
    }

    public function downloadPdf(Request $request) {
        $pengaduan = Pengaduan::select([
            'request_list.id',
            'request_list.tgl_permintaan',
            'request_list.tgl_selesai',
            'users.nama',
            'maskapai.nama AS user_kru',
            'request_list.no_permintaan',
            'request_list.no_pickup',
            'request_list.no_kamar',
            'request_list.status',
            'request_list.jam_pickup',
            'request_list.jam_selesai',
            'laundry_item.id_item AS kode_pakaian',
            'laundry_item.nama AS nama_pakaian',
            'request_detail.jml_item',
            'request_detail.description AS deskripsi',
            'checker.nama AS checked_by',
            'delivery.nama AS delivery_by'
        ])
            ->leftJoin('request_detail', 'request_list.id', '=', 'request_detail.request_list_id')
            ->leftJoin('users', 'request_list.user_id', '=', 'users.id')
            ->leftJoin('users AS checker', 'request_list.checked_by', '=', 'checker.id')
            ->leftJoin('users AS delivery', 'request_list.delivery_by', '=', 'delivery.id')
            ->leftJoin('laundry_item', 'laundry_item.id', '=', 'request_detail.id_item')
            ->leftJoin('maskapai', 'maskapai.id', '=', 'users.user_kru')
            ->when(!empty($request->status), function($q) use ($request) {
                return $q->where('request_list.status', $request->status);
            })
            ->when(!empty($request->user_kru), function($q) use ($request) {
                return $q->where('users.user_kru', $request->user_kru);
            })
            ->when(!empty($request->daily) && ($request->daily == true), function($q) use ($request) {
                return $q->where('request_list.tgl_permintaan', 'like', '%'. date('Y-m-d'). '%');
            })
            ->orderBy('request_list.id', 'desc')
            ->get();

        $userKru = '';
        if (!empty($request->user_kru)) {
            $crew = array_map(function($item) {
                return $item['user_kru'];
            }, $pengaduan->toArray());

            reset($crew);
            $userKru = current($crew);
        }

        $data = [
            'title' => 'PICKUP AND DELIVERY',
            'crew' => $userKru,
            'items' => $pengaduan,
        ];
        // return view('reports.request-list.index', $data);
        // Load the view and pass the data
        $pdf = Pdf::loadView('reports.request-list.index', $data)->setPaper('letter', 'landscape');;
        
        // Return the generated PDF as a download
        $filename = 'Laundry_Report_'.date('Ymd');
        return $pdf->download($filename.'.pdf');
    }
}
