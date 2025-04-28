<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{

    public function category()
    {
        if (Auth::id()) {
            $userId = Auth::id();
            $all_categories = Category::where('admin_or_user_id', '=', $userId)
                ->get()
                ->map(function ($category) {
                    $category->products_count = $category->products()->count();
                    return $category;
                });

            return view('admin_panel.category.category', [
                'all_categories' => $all_categories
            ]);
        } else {
            return redirect()->back();
        }
    }

    public function store_category(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            Category::create([
                'admin_or_user_id' => $userId,
                'category'         => $request->category,
                'category_urdu'    => $request->category_urdu,
                'created_at'       => Carbon::now(),
                'updated_at'       => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Category Added Successfully');
        } else {
            return redirect()->back();
        }
    }
    public function update_category(Request $request)
    {
        if (Auth::id()) {
            $usertype = Auth()->user()->usertype;
            $userId = Auth::id();
            // dd($reques   t);
            $update_id = $request->input('category_id');
            $category = $request->input('category_name');
            $category_urdu = $request->input('category_urdu');
            Category::where('id', $update_id)->update([
                'category'      => $category,
                'category_urdu' => $category_urdu,
                'updated_at'    => Carbon::now(),
            ]);
            return redirect()->back()->with('success', 'Category Updated Successfully');
        } else {
            return redirect()->back();
        }
    }
}
