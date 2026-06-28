<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = Patient::query();

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        $patients = $query->where('is_active', true)
            ->withCount('archivedCharts')
            ->orderBy('last_name')
            ->paginate(25)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'medical_record_number' => 'required|string|unique:patients,medical_record_number',
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'date_of_birth'         => 'nullable|date',
        ]);

        $patient = Patient::create($data);
        AuditLog::record('create_patient', 'patients', $patient->id, null, $patient->toArray());

        return redirect()->route('patients.show', $patient)
            ->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        $charts = $patient->archivedCharts()
            ->with('physicalLocation.shelf.room')
            ->latest('archived_date')
            ->paginate(10);

        return view('patients.show', compact('patient', 'charts'));
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'medical_record_number' => 'required|string|unique:patients,medical_record_number,' . $patient->id,
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'date_of_birth'         => 'nullable|date',
        ]);

        $old = $patient->toArray();
        $patient->update($data);
        AuditLog::record('update_patient', 'patients', $patient->id, $old, $patient->toArray());

        return redirect()->route('patients.show', $patient)->with('success', 'Patient updated.');
    }

    public function search(Request $request)
    {
        $term     = $request->get('q', '');
        $patients = Patient::search($term)
            ->where('is_active', true)
            ->take(20)
            ->get(['id', 'medical_record_number', 'first_name', 'last_name']);

        return response()->json($patients->map(fn($p) => [
            'id'    => $p->id,
            'text'  => "{$p->full_name} (MR#: {$p->medical_record_number})",
            'mr'    => $p->medical_record_number,
            'name'  => $p->full_name,
        ]));
    }
}
