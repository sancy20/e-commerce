<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeValue;
use App\Models\User;
use Illuminate\Http\Request;

class AttributeRequestController extends Controller
{
    public function index()
    {
        $pendingValues = AttributeValue::where('is_approved', false)
            ->with(['attribute', 'requester'])
            ->latest()
            ->get();
            
        return view('admin.attributes.requests', compact('pendingValues'));
    }

    public function approve(AttributeValue $attributeValue)
    {
        $attributeValue->update(['is_approved' => true]);
        return back()->with('success', "Value '{$attributeValue->value}' has been approved.");
    }

    public function destroy(AttributeValue $attributeValue)
    {
        if (!$attributeValue->is_approved) {
            $attributeValue->delete();
            return back()->with('success', 'Request has been rejected and deleted.');
        }
        return back()->with('error', 'Cannot reject an already approved value.');
    }
}