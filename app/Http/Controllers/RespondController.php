<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Respond;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RespondController extends Controller
{
    private $_statuses = ['progress', 'done'];

    // Create a new request detail
    public function store(Request $request)
    {
        $this->validate($request, [
            'pengaduan_id' => 'required|integer',
            'deskripsi' => 'required|string|max:255',
        ]);

        $noTanggapan = 'TP'.date('ym').substr(strtotime('now'), -3, 3);
        $image = null;
        if ($request->file('foto_tanggapan')) {
            $filename = $request->file('foto_tanggapan')->getClientOriginalName();
            $r = $request->file('foto_tanggapan')->move(storage_path('images/'.$noTanggapan), $filename);
            $image = 'images/'.$noTanggapan.'/'.$r->getBasename();
        }

        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
            'no_tanggapan' => $noTanggapan,
            'tgl_tanggapan' => date('Y-m-d'),
            'deskripsi' => $request->deskripsi,
            'foto_tanggapan' => $image,
            'status' => 'progress'
        ]);

        $new = Respond::create($payload);

        $complaint = Complaint::where('id', $request->pengaduan_id)
            ->update(['status' => 'progress']);

        return $this->success($new, 201);
    }

    // Update an existing request detail by ID
    public function update(Request $request, $id)
    {
        $detail = Respond::find($id);

        if (!$detail) {
            return $this->failed([], 'Request detail not found', 404);
        }

        $this->validate($request, [
            'deskripsi' => 'required',
        ]);

        $noTanggapan = $detail->no_tanggapan;
        $image = null;
        if ($request->file('foto_tanggapan')) {
            if (is_file(storage_path($detail->foto_tanggapan))) {
                unlink(storage_path($detail->foto_tanggapan));
            }
            $filename = $request->file('foto_tanggapan')->getClientOriginalName();
            $r = $request->file('foto_tanggapan')->move(storage_path('images/'.$notanggapan), $filename);
            $image = 'images/'.$notanggapan.'/'.$r->getBasename();
        }

        if (!empty($image)) {
            $detail->foto_tanggapan = $image;
        }

        $payload = array_merge($request->all(), [
            'user_id' => $request->auth->id,
        ]);
        
        $detail->update($payload);

        $response = Respond::where('id', $id)
            ->first();

        if (!empty($response->foto_tanggapan)) {
            $response->foto_tanggapan = env('APP_URL', ''). '/api/image?filename='.base64_encode($response->foto_tanggapan);
        }

        return $this->success($response);
    }

    // Delete an existing request detail by ID
    public function destroy($id)
    {
        $detail = RequestDetail::find($id);

        if (!$detail) {
            return $this->failed([], 'Request detail not found', 404);
        }

        $requestList = RequestList::where('id', $detail->id)->first();
        if (in_array($requestList->status, ['checking', 'on_progress', 'delivery', 'done'])) {
            return $this->failed([], 'Tidak bisa untuk delete data, status sudah :'. $requestList->status);
        }

        $detail->delete();

        return $this->success([], 'Request detail deleted successfully');
    }
}
