<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Lead::latest();
        if (array_key_exists($status, Lead::STATUSES)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        return view('admin.leads.index', [
            'leads' => $query->get(),
            'status' => $status,
            'counts' => Lead::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(int $id)
    {
        return view('admin.leads.show', ['lead' => Lead::findOrFail($id)]);
    }

    public function update(Request $request, int $id)
    {
        $lead = Lead::findOrFail($id);
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Lead::STATUSES)),
            'notes' => 'nullable|string|max:5000',
        ]);

        $lead->update($data);

        return redirect()->route('admin.leads.show', $lead->id)->with('success', 'Lead updated.');
    }

    public function destroy(int $id)
    {
        Lead::findOrFail($id)->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }
}
