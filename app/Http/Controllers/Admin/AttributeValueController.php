<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributeValueController extends Controller
{
    public function index()
    {
        $attributeValues = AttributeValue::with('attribute')->orderBy('attribute_id')->orderBy('value')->paginate(15);
        return view('admin.attribute_values.index', compact('attributeValues'));
    }

    public function create()
    {
        $attributes = Attribute::orderBy('name')->get();
        return view('admin.attribute_values.create', compact('attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:255',
                'unique:attribute_values,slug,NULL,id,attribute_id,' . $request->attribute_id,
            ],
        ]);

        AttributeValue::create($request->all());

        return redirect()->route('admin.attribute-values.index')
                         ->with('success', 'Attribute Value created successfully.');
    }

    public function show(AttributeValue $attributeValue)
    {
        $attributeValue->load('attribute');
        return view('admin.attribute_values.show', compact('attributeValue'));
    }

    public function edit(AttributeValue $attributeValue)
    {
        $attributes = Attribute::orderBy('name')->get();
        return view('admin.attribute_values.edit', compact('attributeValue', 'attributes'));
    }

    public function update(Request $request, AttributeValue $attributeValue)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:255',
                'unique:attribute_values,slug,' . $attributeValue->id . ',id,attribute_id,' . $request->attribute_id,
            ],
        ]);

        $attributeValue->update($request->all());

        return redirect()->route('admin.attribute-values.index')
                         ->with('success', 'Attribute Value updated successfully.');
    }

    public function destroy(AttributeValue $attributeValue)
    {
        $attributeValue->delete();

        return redirect()->route('admin.attribute-values.index')
                         ->with('success', 'Attribute Value deleted successfully.');
    }
}