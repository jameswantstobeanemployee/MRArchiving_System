<?php
// app/Http/Controllers/GlobalSearchController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\ArchivedChart;
use App\Models\CheckoutHistory;

class GlobalSearchController extends Controller
{
    /**
     * Perform global search across the system
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'results' => [],
                'message' => 'Please enter at least 2 characters'
            ]);
        }

        $results = [];

        // Search Patients
        $patients = Patient::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('medical_record_number', 'like', "%{$query}%")
            ->with(['archivedCharts' => function($q) {
                $q->where('status', '!=', 'destroyed')->latest();
            }])
            ->limit(10)
            ->get();

        foreach ($patients as $patient) {
            $results[] = [
                'type' => 'patient',
                'id' => $patient->id,
                'title' => $patient->full_name,
                'subtitle' => "MR#: {$patient->medical_record_number}",
                'url' => route('patients.show', $patient),
                'icon' => 'fas fa-user',
                'badge' => $patient->archivedCharts->count() . ' chart(s)',
                'badge_class' => 'info'
            ];
        }

        // Search Charts
        $charts = ArchivedChart::with(['patient'])
            ->where('case_number', 'like', "%{$query}%")
            ->orWhereHas('patient', function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('medical_record_number', 'like', "%{$query}%");
            })
            ->where('status', '!=', 'destroyed')
            ->limit(10)
            ->get();

        foreach ($charts as $chart) {
            $statusColor = match($chart->status) {
                'checked_out' => 'warning',
                'archived' => 'success',
                default => 'info'
            };
            
            $locationText = $chart->physicalLocation 
                ? $chart->physicalLocation->box_code 
                : 'Unknown location';
            
            $results[] = [
                'type' => 'chart',
                'id' => $chart->id,
                'title' => "Case: {$chart->case_number}",
                'subtitle' => "Patient: {$chart->patient->full_name} • Location: {$locationText}",
                'url' => route('charts.show', $chart),
                'icon' => 'fas fa-folder-medical',
                'badge' => ucfirst(str_replace('_', ' ', $chart->status)),
                'badge_class' => $statusColor
            ];
        }

        // Search Checkouts (active checkouts)
        $checkouts = CheckoutHistory::with(['archivedChart.patient'])
            ->where('status', 'active')
            ->where(function($q) use ($query) {
                $q->where('person', 'like', "%{$query}%")
                  ->orWhere('department', 'like', "%{$query}%")
                  ->orWhereHas('archivedChart', function($cq) use ($query) {
                      $cq->where('case_number', 'like', "%{$query}%")
                         ->orWhereHas('patient', function($pq) use ($query) {
                             $pq->where('first_name', 'like', "%{$query}%")
                                ->orWhere('last_name', 'like', "%{$query}%");
                         });
                  });
            })
            ->limit(5)
            ->get();

        foreach ($checkouts as $checkout) {
            $isOverdue = $checkout->is_overdue;
            $results[] = [
                'type' => 'checkout',
                'id' => $checkout->id,
                'title' => "Checked Out: {$checkout->archivedChart->case_number}",
                'subtitle' => "To: {$checkout->person} • Due: {$checkout->expected_return_date->format('M d, Y')}",
                'url' => route('checkout.show', $checkout),
                'icon' => 'fas fa-exchange-alt',
                'badge' => $isOverdue ? 'Overdue' : 'Active',
                'badge_class' => $isOverdue ? 'danger' : 'warning'
            ];
        }

        // Group results by type
        $grouped = [
            'patients' => array_filter($results, fn($r) => $r['type'] === 'patient'),
            'charts' => array_filter($results, fn($r) => $r['type'] === 'chart'),
            'checkouts' => array_filter($results, fn($r) => $r['type'] === 'checkout'),
        ];

        return response()->json([
            'success' => true,
            'query' => $query,
            'total' => count($results),
            'results' => $results,
            'grouped' => $grouped
        ]);
    }

    /**
     * Quick search for autocomplete suggestions
     */
    public function autocomplete(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];

        // Patient suggestions
        $patients = Patient::where('first_name', 'like', "%{$query}%")
            ->orWhere('last_name', 'like', "%{$query}%")
            ->orWhere('medical_record_number', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($patients as $patient) {
            $suggestions[] = [
                'value' => $patient->full_name,
                'label' => "👤 {$patient->full_name} (MR#: {$patient->medical_record_number})",
                'url' => route('patients.show', $patient),
                'type' => 'patient'
            ];
        }

        // Chart suggestions
        $charts = ArchivedChart::with('patient')
            ->where('case_number', 'like', "%{$query}%")
            ->limit(5)
            ->get();

        foreach ($charts as $chart) {
            $suggestions[] = [
                'value' => $chart->case_number,
                'label' => "📋 Case: {$chart->case_number} - {$chart->patient->full_name}",
                'url' => route('charts.show', $chart),
                'type' => 'chart'
            ];
        }

        return response()->json($suggestions);
    }
}