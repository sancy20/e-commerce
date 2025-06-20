<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::orderBy('name')->paginate(10);
        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name',
        ]);

        Attribute::create($request->all());

        return redirect()->route('admin.attributes.index')
                         ->with('success', 'Attribute created successfully.');
    }

    public function show(Attribute $attribute)
    {
        $attribute->load('values');
        return view('admin.attributes.show', compact('attribute'));
    }

    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:attributes,name,' . $attribute->id,
        ]);

        $attribute->update($request->all());

        return redirect()->route('admin.attributes.index')
                         ->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')
                         ->with('success', 'Attribute deleted successfully.');
    }
}