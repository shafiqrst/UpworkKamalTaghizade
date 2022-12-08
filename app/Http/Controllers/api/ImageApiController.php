<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\ImageController;

use App\Models\Image;

class ImageApiController extends ImageController
{
    public function addImage(Request $request)
    {
        $data = $this->validateRequestData($request);
        return response()->json([
            'success'   => true,
            'message'   => 'Image store successfully',
            'data'      => $data
        ], 200);
    }
    public function listImage(Request $request)
    {

        $data = $this->getImageListing();
        return  response()->json($data, 200);
    }
    public function getImage($id)
    {
        $data = $this->getImageById($id);
        return  response()->json($data, 200);
    }
}
