<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EloquentRepositoryInterface
{
    /**
     * Ambil semua data (bisa dengan relasi)
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Ambil semua data dengan pagination
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    /**
     * Cari satu data berdasarkan ID (UUID)
     */
    public function find(string $id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Cari data berdasarkan kolom tertentu
     */
    public function findBy(string $column, mixed $value, array $relations = []): ?Model;

    /**
     * Buat data baru
     */
    public function create(array $payload): ?Model;

    /**
     * Update data berdasarkan ID
     */
    public function update(string $id, array $payload): bool;

    /**
     * Hapus data (Soft delete jika model support)
     */
    public function delete(string $id): bool;
}
