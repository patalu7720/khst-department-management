<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chip;
use App\Models\ChipLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ChipController extends Controller
{
    public function index(Request $request)
    {
        $query = Chip::query();

        // 🔍 Lọc theo material
        if ($request->filled('material')) {
            $query->where('material', 'like', '%' . $request->material . '%');
        }

        // 🔍 Lọc theo batch
        if ($request->filled('batch')) {
            $query->where('batch', 'like', '%' . $request->batch . '%');
        }

        // 🔍 Lọc theo người tạo
        if ($request->filled('created_user')) {
            $query->where('created_user', 'like', '%' . $request->created_user . '%');
        }

        // 🔍 Lọc theo ngày tạo (từ ngày đến ngày)
        $from = $request->input('from_date');
        $to = $request->input('to_date');

        if ($from && $to) {
            $query->whereBetween('created_at', [
                Carbon::parse($from)->startOfDay(),
                Carbon::parse($to)->endOfDay()
            ]);
        }

        return $query->orderByDesc('id')->get();
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
