<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chip;
use App\Models\ChipLog;
use Illuminate\Support\Facades\Auth;

class ChipController extends Controller
{
    public function index()
    {
        return Chip::orderByDesc('id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->only(['material', 'batch', 'quantity']);
        $data['created_user'] = Auth::user()->username;

        $chip = Chip::create($data);

        ChipLog::create([
            'chip_id' => $chip->id,
            'material' => $chip->material,
            'batch' => $chip->batch,
            'quantity' => $chip->quantity,
            'status' => 'Tạo',
            'created_user' => Auth::user()->username,
        ]);

        return $chip;
    }

    public function update(Request $request, Chip $chip)
    {
        $chip->update($request->only('material', 'batch', 'quantity', 'created_user'));

        ChipLog::create([
            'chip_id' => $chip->id,
            'material' => $chip->material,
            'batch' => $chip->batch,
            'quantity' => $chip->quantity,
            'status' => 'Tạo',
            'created_user' => Auth::user()->username,
        ]);

        return $chip;
    }

    public function destroy(Chip $chip)
    {
        ChipLog::create([
            'chip_id' => $chip->id,
            'material' => $chip->material,
            'batch' => $chip->batch,
            'quantity' => $chip->quantity,
            'status' => 'Tạo',
            'created_user' => Auth::user()->username,
        ]);

        $chip->delete();
        return response()->json(['message' => 'Đã xóa']);
    }
}
