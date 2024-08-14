<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandController extends Controller
{

    public function index(Request $request){
        $brands = Brand::latest('id');

        if($request->get('keyword')){
            $brands = $brands->where('name', 'like', '%' . $request->get('keyword') . '%');
        }

        $brands = $brands->paginate(10);
        return view('admin.brands.list', compact('brands'));

    }
    public function create(){
        return view('admin.brands.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands'
        ]);
        if ($validator->passes()) {
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response([
                'status' => true,
                'message' =>'Brand added Successfully.'
            ]);

        }
        else {
            return response([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            session()->flash('error', 'Brand not found');
            return redirect()->route('brands.index');
        }
        $data['brand'] = $brand;
        return view('admin.brands.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);
        if (!$brand) {
            session()->flash('error', 'Brand not found');
            return redirect()->route('brands.index');
        }

        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $brand->id,
            'status' => 'required|in:0,1',
        ]);

        $brand->name = $request->input('name');
        $brand->slug = $request->input('slug');
        $brand->status = $request->input('status');
        $brand->save();

        session()->flash('success', 'Brand updated successfully');
        return redirect()->route('brands.index');
    }

    public function destroy ($id, Request $request){
        $brand = Brand::find($id);
        if (empty($brand)) {
            session()->flash('error', 'Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
    }
    $brand->delete();

    session()->flash('success', 'Brand Deleted Successfully');
    return response([
        'status' => true,
        'message' => 'Brand Deleted Successfully.'
    ]);
}

}
