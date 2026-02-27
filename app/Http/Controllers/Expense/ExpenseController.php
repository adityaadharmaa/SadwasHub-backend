<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Services\Expense\ExpenseService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    protected $expenseService;
    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    public function store(StoreExpenseRequest $request)
    {
        $expense = $this->expenseService->createExpense($request->validated());

        return $this->successResponse(new ExpenseResource($expense), 'Pengeluaran berhasil dicatat beserta bukti nota.', 201);
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $perPage = $request->query('per_page', 10);

        $expenses = $this->expenseService->getAllExpenses($perPage, $search, $category);

        return ExpenseResource::collection($expenses)->additional([
            'message' => 'Daftar pengeluaran berhasil diambil.',
            'status' => 'success'
        ]);
    }

    public function destroy($id)
    {
        try {
            $this->expenseService->deleteExpense($id);
            return $this->successResponse(null, 'Data pengeluaran beserta bukti struk berhasil dihapus.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }
}
