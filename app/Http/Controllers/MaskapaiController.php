<?php

namespace App\Http\Controllers;

use App\Models\Maskapai;
use App\Helpers\Helper;
use Illuminate\Http\Request;

class MaskapaiController extends Controller
{
    // Retrieve all Maskapais
    public function index()
    {
        $items = Maskapai::all();
        return $this->success($items);
    }

    // Retrieve a single Maskapai by ID
    public function show($id)
    {
        $item = Maskapai::find($id);

        if (!$item) {
            return $this->failed([], 'Maskapai not found', 404);
        }

        return $this->success($item);
    }

    // Create a new Maskapai
    public function store(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required|string|max:255',
        ]);

        $item = Maskapai::where('nama', $request->nama)->first();
        if (!empty($item)) {
            return $this->failed([], 'Data sudah ada', 201);
        }

        $newItem = Maskapai::create([
            'code' => 'MS-'. Helper::generateRandomString(3),
            'nama' => $request->nama,
        ]);

        return $this->success($newItem, 201);
    }

    // Update an existing Maskapai by ID
    public function update(Request $request, $id)
    {
        $item = Maskapai::find($id);

        if (!$item) {
            return $this->failed([], 'Maskapai not found', 404);
        }

        $this->validate($request, [
            'nama' => 'sometimes|required|string|max:255',
        ]);

        $item->update($request->all());

        return $this->success($item);
    }

    // Delete an existing Maskapai by ID
    public function destroy($id)
    {
        $item = Maskapai::find($id);

        if (!$item) {
            return $this->failed([], 'Maskapai not found', 404);
        }

        $item->delete();

        return $this->success(['message' => 'Maskapai deleted successfully']);
    }

    public function dropdown()
    {
        $items = Maskapai::select(['id', 'nama'])->get();
        return $this->success($items);
    }
}
