<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;      // Import Attribute model
use App\Models\AttributeValue; // Import AttributeValue model
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    /**
     * Display a listing of the resource (AttributeValues).
     */
    public function index()
    {
        // Eager load the parent attribute for display
        $attributeValues = AttributeValue::with('attribute')->orderBy('attribute_id')->orderBy('value')->paginate(15);
        return view('admin.attribute_values.index', compact('attributeValues'));
    }

    /**
     * Show the form for creating a new resource (AttributeValue).
     */
    public function create()
    {
        // Pass all attributes to the form so user can select parent attribute
        $attributes = Attribute::orderBy('name')->get();
        return view('admin.attribute_values.create', compact('attributes'));
    }

    /**
     * Store a newly created resource in storage (AttributeValue).
     */
    public function store(Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:255',
                // Unique per attribute to prevent "Color: Red" and "Size: Red"
                // But a value must be unique for its parent attribute (slug used for unique check)
                'unique:attribute_values,slug,NULL,id,attribute_id,' . $request->attribute_id,
            ],
        ]);

        AttributeValue::create($request->all());

        return redirect()->route('admin.attribute-values.index')
                         ->with('success', 'Attribute Value created successfully.');
    }

    /**
     * Display the specified resource (AttributeValue).
     */
    public function show(AttributeValue $attributeValue)
    {
        $attributeValue->load('attribute');
        return view('admin.attribute_values.show', compact('attributeValue'));
    }

    /**
     * Show the form for editing the specified resource (AttributeValue).
     */
    public function edit(AttributeValue $attributeValue)
    {
        $attributes = Attribute::orderBy('name')->get(); // For dropdown in edit form
        return view('admin.attribute_values.edit', compact('attributeValue', 'attributes'));
    }

    /**
     * Update the specified resource in storage (AttributeValue).
     */
    public function update(Request $request, AttributeValue $attributeValue)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:255',
                // Unique per attribute, excluding current ID
                'unique:attribute_values,slug,' . $attributeValue->id . ',id,attribute_id,' . $request->attribute_id,
            ],
        ]);

        $attributeValue->update($request->all());

        return redirect()->route('admin.attribute-values.index')
                         ->with('success', 'Attribute Value updated successfully.');
    }

    /**
     * Remove the specified resource from storage (AttributeValue).
     */
    public function destroy(AttributeValue $attributeValue)
    {
        $attributeValue->delete();

        return redirect()->route('admin.attribute-values.index')
                         ->with('success', 'Attribute Value deleted successfully.');
    }
}