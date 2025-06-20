<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index()
    {
        $shippingMethods = ShippingMethod::orderBy('name')->paginate(10);
        return view('admin.shipping_methods.index', compact('shippingMethods'));
    }

    public function create()
    {
        return view('admin.shipping_methods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shipping_methods,name',
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            // 'is_active' => 'boolean',
        ]);

        ShippingMethod::create([
            'name' => $request->name,
            'description' => $request->description,
            'cost' => $request->cost,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.shipping-methods.index')
                         ->with('success', 'Shipping method created successfully.');
    }

    public function show(ShippingMethod $shippingMethod)
    {
        return view('admin.shipping_methods.show', compact('shippingMethod'));
    }

    public function edit(ShippingMethod $shippingMethod)
    {
        return view('admin.shipping_methods.edit', compact('shippingMethod'));
    }

    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:shipping_methods,name,' . $shippingMethod->id,
            'description' => 'nullable|string',
            'cost' => 'required|numeric|min:0',
            // 'is_active' => 'boolean',
        ]);

        $shippingMethod->update([
            'name' => $request->name,
            'description' => $request->description,
            'cost' => $request->cost,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.shipping-methods.index')
                         ->with('success', 'Shipping method updated successfully.');
    }

    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();

        return redirect()->route('admin.shipping-methods.index')
                         ->with('success', 'Shipping method deleted successfully.');
    }
}