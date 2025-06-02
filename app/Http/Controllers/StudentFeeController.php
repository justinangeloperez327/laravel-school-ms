<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\View\View;
use App\Models\StudentFee;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreStudentFeeRequest;
use App\Http\Requests\UpdateStudentFeeRequest;

class StudentFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = StudentFee::with(['student', 'feeCategory', 'payments']);

        // Filter by student
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // Filter by fee category
        if ($request->has('fee_category_id')) {
            $query->where('fee_category_id', $request->fee_category_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $studentFees = $query->latest()->paginate(15);
        $students = Student::all();
        $feeCategories = FeeCategory::all();

        return Inertia::render('student-fees/index', [
            'studentFees' => $studentFees,
            'students' => $students,
            'feeCategories' => $feeCategories,
            'filters' => $request->only(['student_id', 'fee_category_id', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::all();
        $feeCategories = FeeCategory::all();

        return Inertia::render('student-fees/create', [
            'students' => $students,
            'feeCategories' => $feeCategories,
            'studentFee' => new StudentFee(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStudentFeeRequest $request): RedirectResponse
    {
        $studentFee = StudentFee::create($request->validated());

        return redirect()->route('student-fees.show', $studentFee)
            ->with('success', 'Student fee created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(StudentFee $studentFee): View
    {
        $studentFee->load(['student', 'feeCategory', 'payments']);

        return Inertia::render('student-fees/show', [
            'studentFee' => $studentFee,
            'payments' => $studentFee->payments,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentFee $studentFee): View
    {
        $students = Student::all();
        $feeCategories = FeeCategory::all();

        return Inertia::render('student-fees/edit', [
            'studentFee' => $studentFee,
            'students' => $students,
            'feeCategories' => $feeCategories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStudentFeeRequest $request, StudentFee $studentFee): RedirectResponse
    {
        $studentFee->update($request->validated());

        return redirect()->route('student-fees.show', $studentFee)
            ->with('success', 'Student fee updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StudentFee $studentFee): RedirectResponse
    {
        // Check if there are any payments associated with this fee
        if ($studentFee->payments()->exists()) {
            return back()->with('error', 'Cannot delete fee with associated payments.');
        }

        $studentFee->delete();

        return redirect()->route('student-fees.index')
            ->with('success', 'Student fee deleted successfully.');
    }

    /**
     * Mark a student fee as paid.
     */
    public function markAsPaid(StudentFee $studentFee): RedirectResponse
    {
        $studentFee->update([
            'status' => 'paid',
            'paid_date' => now(),
        ]);

        return redirect()->back()->with('success', 'Fee marked as paid.');
    }

    /**
     * Bulk assign fees to students.
     */
    public function bulkAssign(): View
    {
        $feeCategories = FeeCategory::all();
        $students = Student::all();

        return Inertia::render('student-fees/bulk-assign', [
            'feeCategories' => $feeCategories,
            'students' => $students,
            'bulkAssignData' => [
                'fee_category_id' => null,
                'student_ids' => [],
                'due_date' => now()->addMonth()->format('Y-m-d'),
                'amount' => null,
            ],
        ]);
    }

    /**
     * Store bulk assigned fees.
     */
    public function storeBulkAssign(Request $request): RedirectResponse
    {
        $request->validate([
            'fee_category_id' => 'required|exists:fee_categories,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'due_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        $feeCategory = FeeCategory::find($request->fee_category_id);
        $studentIds = $request->student_ids;
        $dueDate = $request->due_date;
        $amount = $request->amount ?? $feeCategory->amount;

        foreach ($studentIds as $studentId) {
            StudentFee::create([
                'student_id' => $studentId,
                'fee_category_id' => $feeCategory->id,
                'amount' => $amount,
                'due_date' => $dueDate,
                'status' => 'unpaid',
            ]);
        }

        return redirect()->route('student-fees.index')
            ->with('success', 'Fees assigned to ' . count($studentIds) . ' students.');
    }
}
