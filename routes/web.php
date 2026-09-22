<?php

use illuminate\Support\Facades\Route;

Route::get('/latihan', function () {
    $nama = 'Muhammad Khaerul SUkandar';
    $nilai = [30, 25, 16, 6, 10];

    $hitungRataRata = function (array $data): float {
        $total = 0;
        foreach ($data as $angka) {
            $total += $angka;
        }
        return $total / count($data);
    };

    $rataRata = $hitungRataRata($nilai);
    if ($rataRata >=75) {
        $status = 'Lulus';
    } else {
        $status = 'Perlu Perbaikan';
    }

    return view ('latihan', compact (
        'nama', 'nilai', 'rataRata', 'status'
        ));
});