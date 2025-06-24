<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('name')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();
        $mainCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        $attributes = Attribute::all();
        return view('admin.categories.create', compact('mainCategories','category','attributes', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        Category::create($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->get();
        $attributes = Attribute::all();
        return view('admin.categories.edit', compact('category', 'categories', 'attributes'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where(function ($query) use ($category) {
                $query->where('id', '!=', $category->id);
            })],
        ]);

        $category->update($request->all());

        if ($request->has('attributes')) {
            $category->attributes()->sync($request->input('attributes'));
        } else {
            $category->attributes()->sync([]);
        }
    
        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');

    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}