<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute; // Import Attribute model
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    /**
     * Display a listing of the resource (Attributes).
     */
    public function index()
    {
        $attributes = Attribute::orderBy('name')->paginate(10);
        return view('admin.attributes.index', compact('attributes'));
    }

    /**
     * Show the form for creating a new resource (Attribute).
     */
    public function create()
    {
        return view('admin.attributes.create');
    }

    /**
     * Store a newly created resource in storage (Attribute).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
        ]);

        Attribute::create($request->all());

        return redirect()->route('admin.attributes.index')
                         ->with('success', 'Attribute created successfully.');
    }

    /**
     * Display the specified resource (Attribute).
     */
    public function show(Attribute $attribute)
    {
        // Eager load its values for display
        $attribute->load('values');
        return view('admin.attributes.show', compact('attribute'));
    }

    /**
     * Show the form for editing the specified resource (Attribute).
     */
    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.edit', compact('attribute'));
    }

    /**
     * Update the specified resource in storage (Attribute).
     */
    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
        ]);

        $attribute->update($request->all());

        return redirect()->route('admin.attributes.index')
                         ->with('success', 'Attribute updated successfully.');
    }

    /**
     * Remove the specified resource from storage (Attribute).
     */
    public function destroy(Attribute $attribute)
    {
        $attribute->delete(); // This will cascade delete related attribute values if onDelete('cascade') is set on migration

        return redirect()->route('admin.attributes.index')
                         ->with('success', 'Attribute deleted successfully.');
    }
}