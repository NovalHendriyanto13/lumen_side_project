<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use App\Models\RequestDetail;
use App\Exports\RequestListExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RequestListController extends Controller
{
    private $_statuses = ['request', 'pickup', 'checking', 'progress', 'delivery', 'done'];
    // Retrieve all request lists
    public function index()
    {
        $user = auth()->user();
        $role = $user->role;
                
        $requests = RequestList::when($role == 'guest', function($q) use ($user) {
            return $q->where('user_id', $user->id);
        })
            ->when($role == 'checker', function($q) use ($user) {
                return $q->whereIn('status', ['pickup']);
            })
            ->when(!in_array($role, ['checker', 'guest']), function($q) use ($user) {
                return $q->whereNotIn('status', ['pickup']);
            })
            ->get();

        return $this->success($requests);
    }

    // Retrieve a single request list by ID
    public function show($id)
    {
        $request = RequestList::find($id);

        if (!$request) {
            return $this->failed([], 'Request not found', 404);
        }

        $detailItems = RequestDetail::select([
            'request_detail.id',
            'request_detail.request_list_id',
            'request_detail.id_item',
            'request_detail.jml_item',
            'request_detail.description',
            'request_detail.image',
            'laundry_item.id_item as item_code',
            'laundry_item.nama'
        ])
            ->join('laundry_item', 'request_detail.id_item', 'laundry_item.id')
            ->where('request_list_id', $id)->get();

        $detailItems->map(function($item) {
            if (!empty($item->image)) {
                $item->image = env('APP_URL', ''). '/api/image?filename='.base64_encode($item->image);
            }
            return $item;
        });

        $request->items = $detailItems;

        return $this->success($request);
    }

    // Create a new request list
    public function store(Request $request)
    {
        $this->validate($request, [
            'no_kamar' => 'required|string|max:5',
            'tgl_selesai' => 'nullable',
            'jam_selesai' => 'nullable',
            'items' => 'required|array'
        ]);

        $tglSelesai = empty($request->tgl_selesai) ? date('Y-m-d', strtotime('+1 day')) : date('Y-m-d', strtotime($request->tgl_selesai));
        $jamSelesai = empty($request->jam_selesai) ? date('H:i:s', strtotime('NOW')) : date('H:i:s', strtotime($request->jam_selesai));
        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
            'no_permintaan' => 'RL'.substr(strtotime('now'), -3, 3),
            'tgl_permintaan' => date('Y-m-d'),
            'tgl_selesai' => $tglSelesai,
            'jam_selesai' => $jamSelesai,
            'status' => 'request'
        ]);
        if ($payload['tgl_selesai'] < $payload['tgl_permintaan']) {
            return $this->failed([], 'Tgl Selesai tidak boleh kurang dari hari ini');
        }
        
        $newRequest = RequestList::create($payload);

        $requestId = $newRequest->id;
        
        $payloadDetail = array_map(function($item) use ($requestId) {
            $item['request_list_id'] = $requestId;
            $item['id_item'] = $item['id'];
            unset($item['item_code']);
            unset($item['nama']);
            unset($item['uniqueId']);
            unset($item['id']);
            return $item;
        }, $request->items);

        RequestDetail::insert($payloadDetail);

        $detailItems = RequestDetail::where('request_list_id', $requestId)->get();
        $newRequest->items = $detailItems;

        return $this->success($newRequest, 201);
    }

    // Update an existing request list by ID
    public function update(Request $request, $id)
    {
        $requestList = RequestList::find($id);

        if (!$requestList) {
            return $this->failed([], 'Request not found', 404);
        }

        $this->validate($request, [
            'no_pickup' => 'string|max:5',
            'no_kamar' => 'sometimes|required|string|max:5',
            'tgl_selesai' => 'nullable|date',
            'jam_selesai' => 'nullable',
        ]);

        $tglSelesai = empty($request->tgl_selesai) ? date('Y-m-d', strtotime('+1 day')) : date('Y-m-d', strtotime($request->tgl_selesai));
        $jamSelesai = empty($request->jam_selesai) ? date('H:i:s', strtotime('NOW')) : date('H:i:s', strtotime($request->jam_selesai));
        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
            'tgl_selesai' => $tglSelesai,
            'jam_selesai' => $jamSelesai,
        ]);
        if ($payload['tgl_selesai'] < $requestList->tgl_permintaan) {
            return $this->failed([], 'Tgl Selesai tidak boleh kurang dari hari ini');
        }

        if ($request->auth->role == 'guest') {
            if (in_array($requestList->status, ['checking', 'on_progress', 'delivery', 'done'])) {
                return $this->failed([], 'Tidak bisa untuk update data, status sudah :'. $requestList->status);
            }
        }      

        $requestList->update($request->all());

        return $this->success($requestList);
    }

    // Delete an existing request list by ID
    public function destroy($id)
    {
        $requestList = RequestList::find($id);

        if (!$requestList) {
            return $this->failed([], 'Request not found', 404);
        }

        $requestList->update(['status' => 'deleted']);

        return $this->success([], 'Request deleted successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $requestList = RequestList::find($id);

        if (!$requestList) {
            return $this->failed([], 'Request not found', 404);
        }

        $currentStatus = $requestList->status;
        $newStatusIndex = (array_search($currentStatus, $this->_statuses)) + 1;
        
        if ($newStatusIndex >= (count($this->_statuses))) {
            return $this->failed([], 'laundry sudah selesai', 401);
        }
        if ($this->_statuses[$newStatusIndex] == 'pickup') {
            $requestList->no_pickup = 'PU'.substr(strtotime('now'), -3, 3);
            $requestList->jam_pickup = date('H:i:s');
        }
        if ($this->_statuses[$newStatusIndex] == 'checking') {
            $requestList->checked_by = auth()->user()->id;
        }
        if ($this->_statuses[$newStatusIndex] == 'delivery') {
            $requestList->delivery_by = auth()->user()->id;
        }
        $requestList->status = $this->_statuses[$newStatusIndex];
        $requestList->save();

        return $this->success($requestList);
    }

    public function downloadReport(Request $request) {
        $requestList = RequestList::select([
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

        $exports = new RequestListExport($requestList);

        return Excel::download($exports, 'report.xlsx');
    }

    public function downloadPdf(Request $request) {
        $requestList = RequestList::select([
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
            }, $requestList->toArray());

            reset($crew);
            $userKru = current($crew);
        }

        $data = [
            'title' => 'PICKUP AND DELIVERY',
            'crew' => $userKru,
            'items' => $requestList,
        ];
        // return view('reports.request-list.index', $data);
        // Load the view and pass the data
        $pdf = Pdf::loadView('reports.request-list.index', $data)->setPaper('letter', 'landscape');;
        
        // Return the generated PDF as a download
        $filename = 'Laundry_Report_'.date('Ymd');
        return $pdf->download($filename.'.pdf');
    }
}
