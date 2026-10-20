
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/mahasiswa', function () {
    try {
        $pdo = DB::connection()->getPdo();

        // Ambil data mahasiswa beserta nama program studi
        $pernyataanMahasiswa = $pdo->prepare(
            'SELECT
                m.nim,
                m.nama,
                m.email,
                m.usia,
                p.nama_program_studi
             FROM mahasiswa AS m
             JOIN program_studi AS p
                ON p.program_studi_id = m.program_studi_id
             ORDER BY m.nim'
        );

        $pernyataanMahasiswa->execute();

        $daftarMahasiswa = $pernyataanMahasiswa->fetchAll(
            \PDO::FETCH_ASSOC
        );

        // Ambil daftar program studi untuk formulir
        $pernyataanProdi = $pdo->prepare(
            'SELECT
                program_studi_id,
                nama_program_studi
             FROM program_studi
             ORDER BY nama_program_studi'
        );

        $pernyataanProdi->execute();

        $daftarProgramStudi = $pernyataanProdi->fetchAll(
            \PDO::FETCH_ASSOC
        );

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi'
        ));

    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('mahasiswa.index');


Route::post('/mahasiswa', function (Request $request) {
    // Validasi input formulir
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:20'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => [
            'required',
            'integer',
            'exists:program_studi,program_studi_id'
        ],
    ], [
        'nim.required' => 'NIM wajib diisi.',
        'nama.required' => 'Nama wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus berupa angka.',
        'program_studi_id.required' => 'Program studi wajib dipilih.',
        'program_studi_id.exists' => 'Program studi tidak ditemukan.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        // Periksa NIM atau email duplikat
        $cekDuplikat = $pdo->prepare(
            'SELECT COUNT(*)
             FROM mahasiswa
             WHERE nim = :nim OR email = :email'
        );

        $cekDuplikat->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan.'
            );
        }

        // Simpan data mahasiswa
        $simpan = $pdo->prepare(
            'INSERT INTO mahasiswa
                (nim, nama, email, usia, program_studi_id)
             VALUES
                (:nim, :nama, :email, :usia, :program_studi_id)'
        );

        $simpan->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
        ]);

        return redirect()
            ->route('mahasiswa.index')
            ->with(
                'berhasil',
                'Data mahasiswa berhasil ditambahkan.'
            );

    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('mahasiswa.store');
