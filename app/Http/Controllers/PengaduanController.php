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
    private $_statuses = ['new', 'progress', 'finished', 'pending'];
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

        $noPengaduan = $pengaduan->no_pengaduan;
        $image = null;
        if ($request->file('foto_pengaduan')) {
            if (is_file(storage_path($pengaduan->foto_pengaduan))) {
                unlink(storage_path($pengaduan->foto_pengaduan));
            }
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

    public function dropdownStatus(Request $request)
    {
        return $this->success($this->_statuses);
    }

    public function downloadReport(Request $request) {
        $pengaduan = Pengaduan::select([
            'pengaduan.tgl_pengaduan',
            'pengaduan.tgl_selesai',
            'users.nik',
            'users.nama',
            'pengaduan.no_pengaduan',
            'pengaduan.deskripsi AS pengaduan',
            'pengaduan.status',
            'tanggapan.no_tanggapan',
            'tanggapan.deskripsi AS tanggapan',
        ])
            ->leftJoin('tanggapan', 'pengaduan.id', '=', 'tanggapan.pengaduan_id')
            ->leftJoin('users', 'pengaduan.user_id', '=', 'users.id')
            ->when(!empty($request->status), function($q) use ($request) {
                return $q->where('pengaduan.status', $request->status);
            })
            ->when(!empty($request->nik), function($q) use ($request) {
                return $q->where('users.nik', $request->nik);
            })
            ->orderBy('pengaduan.id', 'desc')
            ->get();

        $exports = new PengaduanExport($pengaduan);

        return Excel::download($exports, 'report.xlsx');
    }

    public function downloadPdf(Request $request) {
        $pengaduan = Pengaduan::select([
            'pengaduan.tgl_pengaduan',
            'pengaduan.tgl_selesai',
            'users.nik',
            'users.nama',
            'pengaduan.no_pengaduan',
            'pengaduan.deskripsi AS pengaduan',
            'pengaduan.status',
            'tanggapan.no_tanggapan',
            'tanggapan.deskripsi AS tanggapan',
        ])
            ->leftJoin('tanggapan', 'pengaduan.id', '=', 'tanggapan.pengaduan_id')
            ->leftJoin('users', 'pengaduan.user_id', '=', 'users.id')
            ->when(!empty($request->status), function($q) use ($request) {
                return $q->where('pengaduan.status', $request->status);
            })
            ->when(!empty($request->nik), function($q) use ($request) {
                return $q->where('users.nik', $request->nik);
            })
            ->orderBy('pengaduan.id', 'desc')
            ->get();

        $data = [
            'title' => 'Laporan Pengaduan',
            'items' => $pengaduan,
        ];
        // return view('reports.request-list.index', $data);
        // Load the view and pass the data
        $pdf = Pdf::loadView('reports.pengaduan.index', $data)->setPaper('letter', 'landscape');;
        
        // Return the generated PDF as a download
        $filename = 'Pengaduan_Report_'.date('Ymd');
        return $pdf->download($filename.'.pdf');
    }
}
