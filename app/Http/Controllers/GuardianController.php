<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Http\Requests\StoreGuardianRequest;
use App\Http\Requests\UpdateGuardianRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GuardianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $guardians = Guardian::with('students')->paginate(15);
        return Inertia::render('guardians/index', [
            'guardians' => $guardians,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return Inertia::render('guardians/create', [
            'guardian' => new Guardian(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGuardianRequest $request): RedirectResponse
    {
        $guardian = Guardian::create($request->validated());
        return redirect()->route('guardians.show', $guardian)->with('success', 'Guardian created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Guardian $guardian): View
    {
        $guardian->load('students');
        return Inertia::render('guardians/show', [
            'guardian' => $guardian,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guardian $guardian): View
    {
        return Inertia::render('guardians/edit', [
            'guardian' => $guardian,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGuardianRequest $request, Guardian $guardian): RedirectResponse
    {
        $guardian->update($request->validated());
        return redirect()->route('guardians.show', $guardian)->with('success', 'Guardian updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guardian $guardian): RedirectResponse
    {
        // Check if the guardian has students
        if ($guardian->students()->exists()) {
            return back()->with('error', 'Cannot delete guardian with associated students.');
        }

        $guardian->delete();
        return redirect()->route('guardians.index')->with('success', 'Guardian deleted successfully.');
    }
}
