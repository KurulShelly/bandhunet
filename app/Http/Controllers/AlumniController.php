<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use Illuminate\Http\Request;

class AlumniController extends Controller
{

    public function index()
    {
        $alumni = Alumni::latest()->get();
        return view('alumni.index', compact('alumni'));
    }

    public function create()
    {
        return view('alumni.create');
    }

    public function store(Request $request)
    {

        $request->validate([
            'nama' => 'required|string|max:100',
            'prodi' => 'required|string|max:100',
            'tahun_lulus' => 'required|numeric'
        ]);


        Alumni::create([

            'nama' => $request->nama,
            'prodi' => $request->prodi,
            'tahun_lulus' => $request->tahun_lulus,
            'kota' => $request->kota ?? null,
            'pekerjaan' => $request->pekerjaan ?? null,
            'instansi' => $request->instansi ?? null,
            'status_pelacakan' => 'Belum Dilacak'

        ]);

        return redirect()->route('alumni.index')
        ->with('success','Data alumni berhasil ditambahkan');

    }

    public function show($id)
    {
        $alumni = Alumni::findOrFail($id);
        return view('alumni.show', compact('alumni'));
    }


    public function edit($id)
    {
        $alumni = Alumni::findOrFail($id);
        return view('alumni.edit', compact('alumni'));
    }

    public function update(Request $request, $id)
    {

        $request->validate([
            'nama' => 'required|string|max:100',
            'prodi' => 'required|string|max:100',
            'tahun_lulus' => 'required|numeric'
        ]);


        $alumni = Alumni::findOrFail($id);


        $alumni->update([

            'nama' => $request->nama,
            'prodi' => $request->prodi,
            'tahun_lulus' => $request->tahun_lulus,
            'kota' => $request->kota ?? null,
            'pekerjaan' => $request->pekerjaan ?? null,
            'instansi' => $request->instansi ?? null,

        
            'status_pelacakan' => $alumni->status_pelacakan

        ]);


        return redirect()->route('alumni.index')
        ->with('success','Data alumni berhasil diperbarui');

    }


    public function destroy($id)
    {

        $alumni = Alumni::findOrFail($id);

        $alumni->delete();

        return redirect()->route('alumni.index')
        ->with('success','Data alumni berhasil dihapus');

    }

}