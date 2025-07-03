<?php

namespace App\Http\Controllers;

use App\Models\Category;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();  // Get all categories
        return $categories;
    }

    // Show the form for creating a new resource
    public function create()
    {
        return view('categories.create');
    }

    // Store a newly created resource in storage
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Add other validation rules as necessary
        ]);

        // Create and save the category
        $category = Category::create([
            'name' => $validated['name'],
        ]);

        return response()->json($category, 201); 
    }

    // Display the specified resource
    public function show($category)
    {
        error_log($category);

        $categories = Category::all();

        $myCategory = Category::with('auctions.category')->where('id', $category)->firstOrFail();

        error_log($category);

        return view('pages.category', compact('myCategory', 'categories'));
    }

    // Show the form for editing the specified resource
    public function edit(Category $category)
    {
        // Optionally, return a view with the category data
        return view('categories.edit', compact('category'));
    }

    // Update the specified resource in storage
    public function update(Request $request, Category $category)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // Add other validation rules as necessary
        ]);

        // Update the category with new data
        $category->update([
            'name' => $validated['name'],
        ]);

        return response()->json($category);  // Return the updated category
    }

    // Remove the specified resource from storage
    public function destroy(Category $category)
    {
        // Delete the category
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
    }

}
