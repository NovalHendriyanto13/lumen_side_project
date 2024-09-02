<?php

namespace App\Http\Controllers;

use App\Models\LaundryItem;
use Illuminate\Http\Request;

class LaundryItemController extends Controller
{
    // Retrieve all laundry items
    public function index()
    {
        $items = LaundryItem::all();
        return $this->success($items);
    }

    // Retrieve a single laundry item by ID
    public function show($id)
    {
        $item = LaundryItem::find($id);

        if (!$item) {
            return $this->failed([], 'Laundry item not found', 404);
        }

        return $this->success($item);
    }

    // Create a new laundry item
    public function store(Request $request)
    {
        $this->validate($request, [
            'id_item' => 'required|integer',
            'nama' => 'required|string|max:255',
        ]);

        $newItem = LaundryItem::create($request->all());

        return $this->success($newItem, 201);
    }

    // Update an existing laundry item by ID
    public function update(Request $request, $id)
    {
        $item = LaundryItem::find($id);

        if (!$item) {
            return $this->failed([], 'Laundry item not found', 404);
        }

        $this->validate($request, [
            'id_item' => 'sometimes|required|integer',
            'nama' => 'sometimes|required|string|max:255',
        ]);

        $item->update($request->all());

        return $this->success($item);
    }

    // Delete an existing laundry item by ID
    public function destroy($id)
    {
        $item = LaundryItem::find($id);

        if (!$item) {
            return $this->failed([], 'Laundry item not found', 404);
        }

        $item->delete();

        return $this->success(['message' => 'Laundry item deleted successfully']);
    }
}
