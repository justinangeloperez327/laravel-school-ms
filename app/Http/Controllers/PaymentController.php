<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\View\View;
use App\Models\StudentFee;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Payment::with(['studentFee.student', 'studentFee.feeCategory']);

        // Filter by student
        if ($request->has('student_id')) {
            $query->whereHas('studentFee', function($q) use ($request) {
                $q->where('student_id', $request->student_id);
            });
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('payment_date', [$request->from_date, $request->to_date]);
        }

        $payments = $query->latest()->paginate(15);
        $students = Student::all();
        $paymentMethods = ['cash', 'bank transfer', 'credit card', 'mobile money'];

        return Inertia::render('payments/index', [
            'payments' => $payments,
            'students' => $students,
            'paymentMethods' => $paymentMethods,
            'filters' => $request->only(['student_id', 'payment_method', 'from_date', 'to_date']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $studentFees = StudentFee::with(['student', 'feeCategory'])
            ->where('status', '!=', 'paid')
            ->get();
        $paymentMethods = ['cash', 'bank transfer', 'credit card', 'mobile money'];

        return Inertia::render('payments/create', [
            'payment' => new Payment(),
            'studentFees' => $studentFees,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $payment = Payment::create($request->validated());

        // Update the student fee status
        $studentFee = StudentFee::findOrFail($request->student_fee_id);
        $totalPaid = $studentFee->payments->sum('amount') + $payment->amount;

        if ($totalPaid >= $studentFee->amount) {
            $studentFee->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);
        } else {
            $studentFee->update([
                'status' => 'partial',
            ]);
        }

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): View
    {
        $payment->load(['studentFee.student', 'studentFee.feeCategory']);

        return Inertia::render('payments/show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment): View
    {
        $studentFees = StudentFee::with(['student', 'feeCategory'])
            ->where('status', '!=', 'paid')
            ->orWhere('id', $payment->student_fee_id)
            ->get();
        $paymentMethods = ['cash', 'bank transfer', 'credit card', 'mobile money'];

        return Inertia::render('payments/edit', [
            'payment' => $payment,
            'studentFees' => $studentFees,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        // Store the old student fee ID and amount for comparison
        $oldStudentFeeId = $payment->student_fee_id;
        $oldAmount = $payment->amount;

        $payment->update($request->validated());

        // If the student fee ID changed, update both old and new fee statuses
        if ($oldStudentFeeId != $payment->student_fee_id) {
            // Update old student fee status
            $oldStudentFee = StudentFee::findOrFail($oldStudentFeeId);
            $oldTotalPaid = $oldStudentFee->payments->sum('amount');

            if ($oldTotalPaid >= $oldStudentFee->amount) {
                $oldStudentFee->update(['status' => 'paid', 'paid_date' => now()]);
            } elseif ($oldTotalPaid > 0) {
                $oldStudentFee->update(['status' => 'partial']);
            } else {
                $oldStudentFee->update(['status' => 'unpaid', 'paid_date' => null]);
            }
        }

        // Update current student fee status
        $studentFee = StudentFee::findOrFail($payment->student_fee_id);
        $totalPaid = $studentFee->payments->sum('amount');

        if ($totalPaid >= $studentFee->amount) {
            $studentFee->update(['status' => 'paid', 'paid_date' => now()]);
        } elseif ($totalPaid > 0) {
            $studentFee->update(['status' => 'partial']);
        } else {
            $studentFee->update(['status' => 'unpaid', 'paid_date' => null]);
        }

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        // Get the student fee before deleting the payment
        $studentFee = StudentFee::findOrFail($payment->student_fee_id);

        // Delete the payment
        $payment->delete();

        // Recalculate the status of the student fee
        $totalPaid = $studentFee->payments->sum('amount');

        if ($totalPaid >= $studentFee->amount) {
            $studentFee->update(['status' => 'paid']);
        } elseif ($totalPaid > 0) {
            $studentFee->update(['status' => 'partial']);
        } else {
            $studentFee->update(['status' => 'unpaid', 'paid_date' => null]);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Payment deleted successfully.');
    }

    /**
     * Download receipt for payment.
     */
    public function downloadReceipt(Payment $payment)
    {
        $payment->load(['studentFee.student', 'studentFee.feeCategory']);

        // This would use a PDF library like Dompdf or Snappy PDF
        $pdf = PDF::loadView('payments.receipt', compact('payment'));

        return $pdf->download('payment-receipt-' . $payment->id . '.pdf');
    }
}
