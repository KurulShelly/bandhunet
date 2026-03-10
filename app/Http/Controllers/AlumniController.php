<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;

class AlumniController extends Controller
{
    /**
     * Menampilkan semua data alumni
     */
    public function index()
    {
        $alumni = Alumni::all();
        return view('alumni.index', compact('alumni'));
    }

    /**
     * Menampilkan form tambah alumni
     */
    public function create()
    {
        return view('alumni.create');
    }

    /**
     * Menyimpan data alumni baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'prodi' => 'required',
            'tahun_lulus' => 'required'
        ]);

        Alumni::create([
            'nama' => $request->nama,
            'prodi' => $request->prodi,
            'tahun_lulus' => $request->tahun_lulus,
            'kota' => $request->kota,
            'pekerjaan' => $request->pekerjaan,
            'instansi' => $request->instansi,
            'status_pelacakan' => 'Belum Dilacak'
        ]);

        return redirect()->route('alumni.index')
        ->with('success','Data alumni berhasil ditambahkan');
    }

    /**
     * Menampilkan detail alumni
     */
    public function show($id)
    {
        $alumni = Alumni::findOrFail($id);
        return view('alumni.show', compact('alumni'));
    }

    /**
     * Menampilkan form edit alumni
     */
    public function edit($id)
    {
        $alumni = Alumni::findOrFail($id);
        return view('alumni.edit', compact('alumni'));
    }

    /**
     * Mengupdate data alumni
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required',
            'prodi' => 'required',
            'tahun_lulus' => 'required'
        ]);

        $alumni = Alumni::findOrFail($id);

        $alumni->update([
            'nama' => $request->nama,
            'prodi' => $request->prodi,
            'tahun_lulus' => $request->tahun_lulus,
            'kota' => $request->kota,
            'pekerjaan' => $request->pekerjaan,
            'instansi' => $request->instansi,

            // mempertahankan status pelacakan lama
            'status_pelacakan' => $alumni->status_pelacakan ?? 'Belum Dilacak'
        ]);

        return redirect()->route('alumni.index')
        ->with('success','Data alumni berhasil diperbarui');
    }

    /**
     * Menghapus data alumni
     */
    public function destroy($id)
    {
        $alumni = Alumni::findOrFail($id);
        $alumni->delete();

        return redirect()->route('alumni.index')
        ->with('success','Data alumni berhasil dihapus');
    }
}