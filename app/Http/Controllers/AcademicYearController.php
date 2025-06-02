<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('year_start', 'desc')->get();
        return Inertia::render('academic-years/index', [
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('academic_years.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAcademicYearRequest $request)
    {
        $academicYear = AcademicYear::create($request->validated());

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic Year created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicYear $academicYear)
    {
        return Inertia::render('academic-years/show', [
            'academicYear' => $academicYear,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicYear $academicYear)
    {
        return Inertia::render('academic-years/edit', [
            'academicYear' => $academicYear,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $academicYear->update($request->validated());

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic Year updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic Year deleted successfully');
    }

    /**
     * Set academic year as active
     */
    public function setActive(AcademicYear $academicYear)
    {
        // First, set all academic years as inactive
        AcademicYear::where('active', true)->update(['active' => false]);

        // Set this academic year as active
        $academicYear->update(['active' => true]);

        return redirect()->route('academic-years.index')
            ->with('success', 'Academic Year set as active successfully');
    }
}
