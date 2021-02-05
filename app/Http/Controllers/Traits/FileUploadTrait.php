<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

trait FileUploadTrait {
    public function uploadFiles(Request $request, $folder_name) {

        $uploadPath = public_path('uploads/'.$folder_name);
        $thumbPath = public_path('uploads/'.$folder_name.'/thumb');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775);
            mkdir($thumbPath, 0775);
        }

        $finalRequest = $request;

        foreach ($request->all() as $key => $value) {
            if ($request->hasFile($key)) {
                if ($request->has($key . '_max_width') && $request->has($key . '_max_height')) {
                    $filename = rand().time() . '.' . $request->file($key)->getClientOriginalExtension();
                    $file     = $request->file($key);
                    $image    = Image::make($file);

                    if (! file_exists($thumbPath)) {
                        mkdir($thumbPath, 0775, true);
                    }

                    Image::make($file)->resize(120, 80)->save($thumbPath . '/' . $filename);

                    $width  = $image->width();
                    $height = $image->height();

                    if ($width > $request->{$key . '_max_width'} && $height > $request->{$key . '_max_height'}) {
                        $image->resize($request->{$key . '_max_width'}, $request->{$key . '_max_height'});
                    } elseif ($width > $request->{$key . '_max_width'}) {
                        $image->resize($request->{$key . '_max_width'}, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } elseif ($height > $request->{$key . '_max_height'}) {
                        $image->resize(null, $request->{$key . '_max_height'}, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    }
                    $image->save($uploadPath . '/' . $filename);
                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                } else {
                    $filename = rand().time() . '.' . $request->file($key)->getClientOriginalExtension();
                    $request->file($key)->move($uploadPath, $filename);
                    $finalRequest = new Request(array_merge($finalRequest->all(), [$key => $filename]));
                }
            }
        }

        return $finalRequest;
    }
}