<?php

namespace App\Services\Expense;

use App\Repositories\Interfaces\ExpenseRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ExpenseService extends BaseService
{
    protected $expenseRepo;
    public function __construct(ExpenseRepositoryInterface $expenseRepo)
    {
        $this->expenseRepo = $expenseRepo;
    }

    public function createExpense(array $data)
    {
        return $this->atomic(function () use ($data) {
            // 1. Pisahkan file 'receipt' dari data utama
            $receiptFile = null;
            if (isset($data['receipt'])) {
                $receiptFile = $data['receipt'];
                unset($data['receipt']); //Hapus dari array agar tidak error saat create expense
            }

            // 2. Simpan data pengeluaran (Expense) ke database
            $expense = $this->expenseRepo->create($data);

            // 3. Jika ada file struk, simpan fisiknya dan catat tabel Attachments
            if ($receiptFile) {
                $extension = strtolower($receiptFile->getClientOriginalExtension());
                // Simpan file ke folder storage/app/public/receipts
                $path = '';

                // 4. Logika kompresi foto
                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                    // Jika Gambar: Kompres menggunakan Intervention Image
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($receiptFile->getPathName());

                    // Kecilkan ukuran gambar jika lebarnya lebih dari 800px (tinggi menyesuaikan rasio otomatis)
                    $image->scaleDown(width: 800);

                    // Ubah format menjadi JPG dan turunkan kualitas/ukuran filenya menjadi 75%
                    $encoded = $image->toJpeg(75);

                    // Buat nama file unik
                    $filename = 'receipts/' . uniqid() . '.jpg';

                    // Simpan file hasil kompresi ke Storage public
                    Storage::disk('public')->put($filename, (string) $encoded);

                    $path = $filename;
                    $extension = 'jpg';
                } else {
                    // Jika PDF: Simpan normal tanpa kompresi
                    $path = $receiptFile->store('receipts', 'public');
                }

                // Simpan ke gudang utama (table attachments) menggunakan relasi morphMany
                $expense->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $receiptFile->getClientOriginalExtension()
                ]);
            }

            // Load data attachements agar bisa dilihat saat response
            return $expense->load('attachments');
        });
    }
}
