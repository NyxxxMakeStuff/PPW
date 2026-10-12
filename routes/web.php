<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/mahasiswa');

Route::get('/mahasiswa/{nim?}', function (?string $nim = null) {
    try {
        $pdo = DB::connection()->getPdo();

        $sql = 'SELECT 
                    m.nim,
                    m.nama,
                    m.email,
                    m.usia,
                    p.nama_program_studi
                FROM mahasiswa AS m
                JOIN program_studi AS p 
                    ON p.program_studi_id = m.program_studi_id';

        if ($nim !== null) {
            $sql .= ' WHERE m.nim = :nim';
        }

        $sql .= ' ORDER BY m.nim';

        $statement = $pdo->prepare($sql);

        if ($nim !== null) {
            $statement->execute([
                'nim' => $nim
            ]);
        } else {
            $statement->execute();
        }

        $daftarMahasiswa = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'nim'
        ));

    } catch (\Throwable $error) {
        report($error);
        return response(
            'Koneksi atau query basis data gagal. Periksa file .env dan layanan MySQL.',
            500
        );
    }
});