<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TempImage;
use Nette\Utils\Image;

class TempImageController extends Controller
{
    public function create(Request $request)
    {
        $image = $request->image; // Use file() method to retrieve the uploaded file

        if (!empty($image)) {
            $ext = $image->getClientOriginalExtension();
            $newName = time() . '.' . $ext;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path() . '/temp', $newName);

            //Generate Thumbnail
            $sourcePath = public_path().'/temp/'.$newName;
            $destPath = public_path().'/temp/thumb/'.$newName;
            $image = Image::make($sourcePath);
            $image->fit(300,257);
            $image->save($destPath);

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'imagePath' => asset('/temp/thumb/'.$newName),
                'message' => 'Image uploaded successfully'
            ]);
        }
    }
}
