<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AttributeValueRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttributeRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => 'required|string|max:100',
        ]);
        
        $existing = AttributeValueRequest::where('attribute_id', $validated['attribute_id'])
            ->where('value', $validated['value'])
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false, 
                'message' => 'This value has already been approved or is currently pending review.'
            ], 422);
        }

        AttributeValueRequest::create([
            'vendor_id' => Auth::id(),
            'attribute_id' => $validated['attribute_id'],
            'value' => $validated['value'],
        ]);

        return response()->json(['success' => true, 'message' => 'Your request has been submitted for approval.']);
    }
}