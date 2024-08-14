<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{

    public function index(Request $request)
    {
        $subcategories = SubCategory::select('sub_categories.*', 'categories.name as categoryName')
            ->latest('sub_categories.id')
            ->leftJoin('categories', 'categories.id', 'sub_categories.category_id');

        if (!empty($request->get('keyword'))) {
            $subcategories = $subcategories->where('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
            $subcategories = $subcategories->orwhere('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
        }

        $subcategories = $subcategories->paginate(10);

        return view('admin.sub-category.list', compact('subcategories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub-category.create', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->passes()) {
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category_id;
            $subCategory->save();

            session()->flash('success', 'Subcategory Created Successfully');

            return response([
                'status' => true,
                'message' => 'Subcategory Created Successfully.'
            ]);
        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }


    public function edit($id)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            return redirect()->route('sub-categories.index')->with('error', 'Record not found');
        }

        $categories = Category::orderBy('name', 'ASC')->get();

        return view('admin.sub-category.edit', compact('subCategory', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $subCategory = SubCategory::find($id);
        if (!$subCategory) {
            return redirect()->route('sub-categories.index')->with('error', 'Record not found');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,' . $subCategory->id,
            'category_id' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $subCategory->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'status' => $request->status,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('sub-categories.index')->with('success', 'Subcategory Updated Successfully.');
    }


    public function destroy ($id, Request $request){
        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            session()->flash('error', 'Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
    }
    $subCategory->delete();

    session()->flash('success', 'Subcategory Deleted Successfully');
    return response([
        'status' => true,
        'message' => 'Subcategory Deleted Successfully.'
    ]);
}
}
