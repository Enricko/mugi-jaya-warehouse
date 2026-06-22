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
    public function index(): View
    {
        $projects = Project::withCount('shipments')->orderByraw("FIELD(status,'active','planning','on_hold','completed')")->get();

        return view('projects.index', compact('projects'));
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
