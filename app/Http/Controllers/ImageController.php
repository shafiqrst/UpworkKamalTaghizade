<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

use App\Models\Image;

class ImageController extends Controller
{
    private function store(Request $request)
    {
        $data = new Image();
        if ($request->file('sImage')) {
            $file = $request->file('sImage');
            $filename = date('YmdHis') . $file->getClientOriginalName();
            // $file->storeAs(storage_path('private'), $filename);
            $file->move(storage_path('private'), $filename);
            $data['image'] = $filename;
            $data['description'] = $request->input('sDescription');;
            $data['name'] = $request->input('sName');
            $data['type'] = Config::get('constants.active'); // can be pdf ,doc etc 
        }
        if ($data->save()) {
            return array(
                'sName' => $data['name'],
                'sDescription' => $data['description'],
                'type' => $data['type'],
            );
        } else {
            $this->throwError('Saving errors', '');
        }
    }
    protected function getImageById($id)
    {
        $result = Image::find($id);

        if (!empty($result)) {
            $disk = Storage::disk('local');
            return array(
                'sName' => $result->name,
                'sDescription' => $result->description,
                'sImage' => $disk->temporaryUrl($result->image, now()->addMinutes(10))
            );
        }
    }
    protected function getImageListing()
    {
        return Image::paginate(Config::get('constants.imagePagination'));
    }

    protected function throwError($message = '', $data = '')
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => $message,
            'data'      => $data
        ]));
    }

    protected function validateRequestData(Request $request)
    {
        if ($this->checkValidation($request->all())) {
            return $this->store($request);
        }
    }

    protected function checkValidation($data)
    {
        $validator =  Validator::make(
            $data,
            $this->rules(),
            $this->messages()

        );
        if ($validator->fails()) {
            $this->throwError('Validation errors', $validator->errors());
        }
        return   $validator;
    }

    protected function rules()
    {
        return [
            'sName' => 'required|max:50',
            'sDescription' => 'required|max:250',
            'sImage' => 'required|max:5120|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    protected function messages()
    {
        return [
            'sName.required' => 'Name is required',
            'sName.max' => 'Name should not exceed 50 character',
            'sDescription.max' => 'Description should not exceed 250 character',
            'sDescription.required' => 'Description is required',
            'sImage.required' => 'Image is required',
            'sImage.max' => 'Maximum file size to upload is 5MB (5120 KB)'
        ];
    }
}
