<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use App\Models\RequestDetail;
use App\Exports\RequestListExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RequestListController extends Controller
{
    private $_statuses = ['request', 'pickup', 'checking', 'progress', 'delivery', 'done'];
    private $_allow = 'guest';
    // Retrieve all request lists
    public function index()
    {
        $requests = RequestList::all();
        return $this->success($requests);
    }

    // Retrieve a single request list by ID
    public function show($id)
    {
        $request = RequestList::find($id);

        if (!$request) {
            return $this->failed([], 'Request not found', 404);
        }

        $detailItems = RequestDetail::where('request_list_id', $id)->get();
        $request->items = $detailItems;

        return $this->success($request);
    }

    // Create a new request list
    public function store(Request $request)
    {
        $this->validate($request, [
            'no_kamar' => 'required|string|max:5',
            'tgl_selesai' => 'nullable',
            'items' => 'required|array'
        ]);

        $tglSelesai = empty($request->tgl_selesai) ? date('Y-m-d', strtotime('+1 day')) : date('Y-m-d', strtotime($request->tgl_selesai));
        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
            'no_permintaan' => 'RL'.substr(strtotime('now'), -3, 3),
            'tgl_permintaan' => date('Y-m-d'),
            'tgl_selesai' => $tglSelesai,
            'status' => 'request'
        ]);
        if ($payload['tgl_selesai'] < $payload['tgl_permintaan']) {
            return $this->failed([], 'Tgl Selesai tidak boleh kurang dari hari ini');
        }

        $newRequest = RequestList::create($payload);

        $requestId = $newRequest->id;

        $payloadDetail = array_map(function($item) use ($requestId) {
            $item['request_list_id'] = $requestId;
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
        ]);

        $tglSelesai = empty($request->tgl_selesai) ? date('Y-m-d', strtotime('+1 day')) : date('Y-m-d', strtotime($request->tgl_selesai));
        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
            'tgl_selesai' => $tglSelesai,
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
            'request_list.no_permintaan',
            'request_list.no_pickup',
            'request_list.no_kamar',
            'request_list.status',
            'laundry_item.id_item AS kode_pakaian',
            'laundry_item.nama AS nama_pakaian',
            'request_detail.description AS deskripsi',
            'request_detail.jml_item'
        ])
            ->join('request_detail', 'request_list.id', '=', 'request_detail.request_list_id')
            ->join('users', 'request_list.user_id', '=', 'users.id')
            ->join('laundry_item', 'laundry_item.id', '=', 'request_detail.id_item')
            ->orderBy('request_list.id', 'desc')
            ->get();

        $exports = new RequestListExport($requestList);

        return Excel::download($exports, 'report.xlsx');
    }
}
