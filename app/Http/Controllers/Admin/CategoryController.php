<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('name')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $mainCategories = Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.categories.create', compact('mainCategories'));
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
        $mainCategories = Category::whereNull('parent_id')->where('id', '!=', $category->id)->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'mainCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->where(function ($query) use ($category) {
                $query->where('id', '!=', $category->id);
            })],
        ]);

        if ($request->parent_id == $category->id) {
            return redirect()->back()->withErrors(['parent_id' => 'A category cannot be its own parent.'])->withInput();
        }

        $category->update($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}