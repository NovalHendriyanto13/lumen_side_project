<?php

namespace App\Http\Controllers;

use App\Models\RequestList;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class RequestListController extends Controller
{
    private $_statuses = ['request', 'pickup', 'checking', 'on_progress', 'delivery', 'done'];
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

        return $this->success($request);
    }

    // Create a new request list
    public function store(Request $request)
    {
        $this->validate($request, [
            'no_kamar' => 'required|string|max:5',
            'tgl_selesai' => 'nullable'
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

        $this->validate($request, [
            'status' => 'string|max:10',
        ]);

        $requestList->update($request->all());

        return $this->success($requestList);
    }

    public function createWithDetail(Request $request)
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
    }
}
