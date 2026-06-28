<?php

namespace App\Http\Controllers;

use App\Models\ArchivedChart;
use App\Models\CheckoutHistory;
use App\Models\SystemSetting;
use App\Services\CheckoutService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    public function index(Request $request)
    {
        $query = CheckoutHistory::with(['archivedChart.patient', 'checkedOutBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->department, fn($q) => $q->where('department', 'like', "%{$request->department}%"))
            ->when($request->get('overdue'), fn($q) => $q->overdue());

        $checkouts = $query->orderByDesc('checked_out_at')->paginate(25)->withQueryString();

        return view('checkout.index', compact('checkouts'));
    }
    
    public function selectChart()
    {
        return redirect()->route('charts.index')
            ->with('info', 'Search for a chart below, then click it to check it out.');
    }

    public function create(ArchivedChart $chart)
    {
        $defaultDays = SystemSetting::getValue('checkout_default_loan_days', 14);
        $maxDays     = SystemSetting::getValue('checkout_max_loan_days', 30);

        return view('checkout.create', compact('chart', 'defaultDays', 'maxDays'));
    }

    public function store(Request $request, ArchivedChart $chart)
    {
        $maxDays = SystemSetting::getValue('checkout_max_loan_days', 30);

        $data = $request->validate([
            'department'          => 'required|string|max:100',
            'person'              => 'required|string|max:100',
            'purpose'             => 'required|string|max:255',
            'expected_return_date'=> "required|date|after_or_equal:today|before_or_equal:" . now()->addDays($maxDays)->format('Y-m-d'),
            'notes'               => 'nullable|string',
        ]);

        try {
            $this->checkoutService->checkout($chart, $data);
            return redirect()->route('charts.show', $chart)->with('success', 'Chart checked out successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function checkin(Request $request, ArchivedChart $chart)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            $this->checkoutService->checkin($chart, $data['notes'] ?? null);
            return redirect()->route('charts.show', $chart)->with('success', 'Chart returned successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(CheckoutHistory $checkout)
    {
        $checkout->load(['archivedChart.patient', 'checkedOutBy', 'returnedBy']);
        return view('checkout.show', compact('checkout'));
    }
}
