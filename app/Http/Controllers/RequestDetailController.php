<?php

namespace App\Http\Controllers;

use App\Models\RequestDetail;
use Illuminate\Http\Request;

class RequestDetailController extends Controller
{
    // Retrieve all request details
    public function index()
    {
        $details = RequestDetail::all();
        return $this->success($details);
    }

    // Retrieve a single request detail by ID
    public function show($id)
    {
        $detail = RequestDetail::find($id);

        if (!$detail) {
            return $this->failed([], 'Request detail not found', 404);
        }

        return $this->success($detail);
    }

    // Create a new request detail
    public function store(Request $request)
    {
        $this->validate($request, [
            'request_list_id' => 'required|integer',
            'id_item' => 'required|integer',
            'jml_item' => 'required|integer',
            'description' => 'required|string|max:255',
        ]);

        $newDetail = RequestDetail::create($request->all());

        return $this->success($newDetail, 201);
    }

    // Update an existing request detail by ID
    public function update(Request $request, $id)
    {
        $detail = RequestDetail::find($id);

        if (!$detail) {
            return $this->failed([], 'Request detail not found', 404);
        }

        $this->validate($request, [
            'request_list_id' => 'sometimes|required|integer',
            'id_item' => 'sometimes|required|integer',
            'jml_item' => 'sometimes|required|integer',
            'description' => 'sometimes|required|string|max:255',
        ]);

        $detail->update($request->all());

        return $this->success($detail);
    }

    // Delete an existing request detail by ID
    public function destroy($id)
    {
        $detail = RequestDetail::find($id);

        if (!$detail) {
            return $this->failed([], 'Request detail not found', 404);
        }

        $detail->delete();

        return $this->success([], 'Request detail deleted successfully');
    }
}
