<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use App\Models\WarehouseStock;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::withCount('shipments')->orderByraw("FIELD(status,'active','planning','on_hold','completed')")->get();

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project): View
    {
        $stocks = WarehouseStock::with(['material', 'warehouse'])->where('project_id', $project->id)->get();
        $shipments = $project->shipments()->with(['driver', 'warehouse'])->latest()->get();
        $consumption = Transaction::with('material')->where('type', 'consumption')->where('project_id', $project->id)->latest()->get();

        return view('projects.show', compact('project', 'stocks', 'shipments', 'consumption'));
    }
}
