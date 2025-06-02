<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\View\View;
use App\Models\FeeCategory;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreFeeCategoryRequest;
use App\Http\Requests\UpdateFeeCategoryRequest;

class FeeCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $feeCategories = FeeCategory::withCount(['studentFees', 'payments'])->latest()->paginate(10);
        return Inertia::render('fee-categories/index', [
            'feeCategories' => $feeCategories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return Inertia::render('fee-categories/create', [
            'feeCategory' => new FeeCategory(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFeeCategoryRequest $request): RedirectResponse
    {
        FeeCategory::create($request->validated());
        return redirect()->route('fee-categories.index')
            ->with('success', 'Fee category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FeeCategory $feeCategory): View
    {
        $feeCategory->load(['studentFees.student', 'payments']);
        return Inertia::render('fee-categories/show', [
            'feeCategory' => $feeCategory,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FeeCategory $feeCategory): View
    {
        return Inertia::render('fee-categories/edit', [
            'feeCategory' => $feeCategory,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFeeCategoryRequest $request, FeeCategory $feeCategory): RedirectResponse
    {
        $feeCategory->update($request->validated());
        return redirect()->route('fee-categories.index')
            ->with('success', 'Fee category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FeeCategory $feeCategory): RedirectResponse
    {
        // Check if this fee category has any associated student fees
        if ($feeCategory->studentFees()->exists()) {
            return back()->with('error', 'Cannot delete fee category with associated student fees.');
        }

        $feeCategory->delete();
        return redirect()->route('fee-categories.index')
            ->with('success', 'Fee category deleted successfully.');
    }
}
