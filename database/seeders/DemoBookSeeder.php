<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class DemoBookSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['LAB-001','Dasar REST API','Tim Laboratorium Informatika',2026,'pemrograman',5],
            ['LAB-002','Praktikum Laravel 12','Tim Laboratorium Informatika',2026,'pemrograman',4],
            ['LAB-003','Konsep Jaringan Komputer','Tim Laboratorium Informatika',2025,'jaringan',7],
            ['LAB-004','Pengantar Data Mining','Tim Laboratorium Informatika',2026,'data-sains',6],
            ['LAB-005','Mengenal JSON','Tim Laboratorium Informatika',2025,'pemrograman',8],
            ['LAB-006','Dasar Keamanan API','Tim Laboratorium Informatika',2026,'jaringan',3],
            ['LAB-007','Panduan Menulis Laporan','Tim Laboratorium Informatika',2024,'umum',4],
            ['LAB-008','Latihan Python','Tim Laboratorium Informatika',2026,'data-sains',5],
        ];
        foreach ($rows as [$kode, $judul, $penulis, $tahun, $kategori, $stok]) {
            Book::firstOrCreate(['kode' => $kode], compact('judul','penulis','tahun','kategori','stok'));
        }
    }
}
