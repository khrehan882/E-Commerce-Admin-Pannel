<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;


class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::latest();

        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
        }

        $categories = $categories->paginate(10);

        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if ($validator->passes()) {
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            // Save image here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '.' . $ext;
                $spath = public_path() . '/temp/' . $tempImage->name;
                $dpath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($spath, $dpath);

                $category->image = $newImageName;
                $category->save();

                //Generate Image Thumbnail
                $dpath = public_path() . '/uploads/category/thumb/' . $newImageName;
                $img = Image::make($spath);
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dpath);

                $category->image = $newImageName;
                $category->save();
            }

            $request->session()->put('success', 'Category Added Successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category Added Successfully',
            ]);
        } else {
            // Validation failed, return errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }


    public function edit($categoryId, Request $request)
    {
        // Fetch the category based on the provided categoryId
        $category = Category::find($categoryId);

        if (!$category) {
            return redirect()->route('categories.index');
        }

        return view('admin.category.edit', compact('category'));
    }


    public function update($categoryId, Request $request)
    {
        $category = Category::find($categoryId);

        if (!$category) {
            session()->flash('error', 'Category not found');
            return response()->json([
                'status' => false,
                'notfound' => true,
                'message' => 'Category not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $category->id . ',id',
        ]);

        if ($validator->passes()) {
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            $oldImage = $category->image;

            // Save image here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '-' . time() . '.' . $ext;
                $spath = public_path() . '/temp/' . $tempImage->name;
                $dpath = public_path() . '/uploads/category/' . $newImageName;
                File::copy($spath, $dpath);

                //Generate Image Thumbnail
                $dpath = public_path() . '/uploads/category/thumb/' . $newImageName;
                $img = Image::make($spath);
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($dpath);

                $category->image = $newImageName;
                $category->save();
                //Delete Old Images Here
                File::delete(public_path() . '/uploads/category/thumb/' . $oldImage);
                File::delete(public_path() . '/uploads/category/' . $oldImage);
            }

            session()->flash('success', 'Category Updates successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category Updated Successfully',
            ]);
        } else {
            // Validation failed, return errors
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($categoryId, Request $request)
    {
        $category = Category::find($categoryId);
        if (empty($category)) {
            session()->flash('error', 'Category not found');
            return response()->json([
                'status' => true,
                'message' => 'Category not found'
            ]);
        }

        File::delete(public_path() . '/uploads/category/thumb/' . $category->image);
        File::delete(public_path() . '/uploads/category/' . $category->image);
        $category->delete();

        // Flash a success message using the session() helper
        session()->flash('success', 'Category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
