<?php

namespace App\Http\Controllers;

use App\Models\Term;
use Inertia\Inertia;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTermRequest;
use App\Http\Requests\UpdateTermRequest;

class TermController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $academicYearId = $request->query('academic_year_id');

        $query = Term::with('academicYear');

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $terms = $query->orderBy('start_date')->get();
        $academicYears = AcademicYear::orderBy('year_start', 'desc')->get();

        return Inertia::render('terms/index', [
            'terms' => $terms,
            'academicYears' => $academicYears,
            'filters' => $request->only(['academic_year_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $academicYears = AcademicYear::orderBy('year_start', 'desc')->get();

        return Inertia::render('terms/create', [
            'academicYears' => $academicYears,
            'term' => new Term(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTermRequest $request)
    {
        $term = Term::create($request->validated());

        return redirect()->route('terms.index')
            ->with('success', 'Term created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Term $term)
    {
        $term->load('academicYear');
        return Inertia::render('terms/show', [
            'term' => $term,
            'academicYear' => $term->academicYear,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Term $term)
    {
        $academicYears = AcademicYear::orderBy('year_start', 'desc')->get();
        return Inertia::render('terms/edit', [
            'term' => $term,
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTermRequest $request, Term $term)
    {
        $term->update($request->validated());

        return redirect()->route('terms.index')
            ->with('success', 'Term updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Term $term)
    {
        $term->delete();

        return redirect()->route('terms.index')
            ->with('success', 'Term deleted successfully');
    }
}
