<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::withCount('shipments');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        } else {
            $query->orderByRaw("CASE status WHEN 'active' THEN 1 WHEN 'planning' THEN 2 WHEN 'on_hold' THEN 3 WHEN 'completed' THEN 4 ELSE 5 END");
        }

        return view('projects.index', [
            'projects' => $query->get(),
            'statusFilter' => $status ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'client_name' => 'nullable|string|max:150',
            'location' => 'nullable|string|max:200',
            'status' => 'required|in:planning,active,on_hold,completed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $project = Project::create($data);

        return redirect()->route('projects.show', $project)->with('success', "Proyek {$project->name} berhasil dibuat.");
    }

    public function show(Project $project): View
    {
        $stocks = WarehouseStock::with(['material', 'warehouse'])->where('project_id', $project->id)->get();
        $shipments = $project->shipments()->with(['driver', 'warehouse'])->latest()->get();
        $consumption = Transaction::with('material')->where('type', 'consumption')->where('project_id', $project->id)->latest()->get();

        return view('projects.show', compact('project', 'stocks', 'shipments', 'consumption'));
    }
}
