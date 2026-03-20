<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
  public function store(Request $request)
  {
    $request->validate(['name' => 'required|string|max:50|unique:categories,name']);

    $category = Category::create(['name' => $request->name]);

    return response()->json([
      'uuid' => $category->uuid,
      'slug' => $category->slug,
      'name' => $category->name,
    ]);
  }

  public function destroy(Category $category)
  {
    $category->delete();

    return response()->json(['success' => true]);
  }
}
