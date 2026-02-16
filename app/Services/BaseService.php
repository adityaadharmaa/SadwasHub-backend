<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class BaseService
{
    /**
     * Helper untuk menjalankan logic di dalam Database Transaction.
     * Jika terjadi error, otomatis rollback.
     */
    protected function atomic(callable $callback)
    {
        DB::beginTransaction();

        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error("Transaction Failed in Service: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
