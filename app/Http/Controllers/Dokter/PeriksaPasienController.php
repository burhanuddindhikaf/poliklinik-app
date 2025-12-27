<?php

namespace App\Http\Controllers\dokter;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa', 'periksa'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    public function create($id)
    {
        $obats = Obat::all();

        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'obat_json'     => 'required',
            'catatan'       => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true);


        try {
        DB::transaction(function () use ($request, $obatIds) {

            // 1️⃣ Ambil & LOCK data obat (biar aman dari race condition)
            $obats = Obat::whereIn('id', $obatIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 2️⃣ VALIDASI stok dulu
            foreach ($obatIds as $idObat) {
                if (!isset($obats[$idObat])) {
                    throw new \Exception("Obat tidak ditemukan.");
                }

                if ($obats[$idObat]->stok <= 0) {
                    throw new \Exception(
                        "Stok obat '{$obats[$idObat]->nama_obat}' habis."
                    );
                }
            }


        $periksa = Periksa::create([
            'id_daftar_poli' => $request->id_daftar_poli,
            'tgl_periksa'    => now(),
            'catatan'        => $request->catatan,
            'biaya_periksa'  => $request->biaya_periksa + 150000,
        ]);

        foreach ($obatIds as $idObat) {
            DetailPeriksa::create([
                'id_periksa' => $periksa->id,
                'id_obat'    => $idObat,
            ]);
                Obat::where('id', $idObat)->decrement('stok', 1);
        }
});

    } catch (\Throwable $e) {
    return redirect()
        ->back()
        ->withInput()
        ->withErrors(['stok' => $e->getMessage()])
        ->with('error', $e->getMessage());

}

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }
}
