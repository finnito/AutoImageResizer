<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

$router->get('/{constraint}', function (Request $request, $constraint) {
    $parsedURL = parse_url($request->u);
    $info = explode("/", $parsedURL["path"]);
    $file = end($info);
    $splitFile = explode(".", $file);
    $filename = $splitFile[0];
    $extension = $splitFile[1];

    $headers = array(
        "Cache-Control" => "public",
        "Cache-Control" => "max-age=31536000",
        "Cache-Control" => "must-revalidate",
    );

    /**
     * Only allow images from a given host
     * in the .env file.
     *
     * If not set, allow all images.
     * Not recommended.
     **/
    if (env("URL_CONSTRAINT") != null) {
        if (!in_array($parsedURL["host"], explode(",", env("URL_CONSTRAINT")))) {
            return response()->json(['error' => 'Not Authorized: Images from this domain are not authorized.'], 403);
        }
    }

    /**
     * Check if the image is of type
     * jpg, jpeg, png, webp.
     * If not, GD can't use it and
     * therefore don't proceed.
     **/
    if (!in_array($extension, array("jpg", "jpeg", "webp", "png"))) {
        return response()->json(['error' => "Unsupported Media Type: Only allows images of type jpg, jpeg, webp and png."], 415);
    }
    
    /**
     * Order of operations
     * 1. Does the specific file exist?
     *      yes - return it
     *      no  - continue
     * 2. Does the base file exist?
     *      yes - continue
     *      no  - save it to disk
     * 3. Resize the image
     * 4. Return the image
     **/
    if ($constraint == "full") {
        $specificFileName = $file;
    } else {
        $specificFileName = $filename . "-" . $constraint . "." . $extension;
    }
    
    $specificFileExists = Storage::disk("local")->exists("{$parsedURL['host']}/".$specificFileName);
    if ($specificFileExists) {
        return Storage::disk("local")->response("{$parsedURL['host']}/".$specificFileName, "", $headers);
    }
            

    /**
     * The specific image was not found, so we
     * need to check if the base image exists
     * on disk so it can be resized.
     * If it doesn't exist, save it.
     **/
    $baseImageExists = Storage::disk("local")->exists("{$parsedURL['host']}/".$file);
    if (!$baseImageExists) {
        Storage::put("{$parsedURL['host']}/{$file}", file_get_contents(
            $parsedURL["scheme"]
            ."://"
            .$parsedURL["host"]
            .$parsedURL["path"]
        ));
    }

    /**
     * Next, we're going to check if the
     * specific image exists, and if not,
     * creating it and saving it to disk.
     **/
    if ($constraint != "full") {
        $specificFileName = $filename . "-" . $constraint . "." . $extension;
        $resizedImage = Image::make(
            storage_path("app/{$parsedURL['host']}/{$file}")
        );

        $resizedImage->orientate();

        /**
         * If portrait, resize on the width,
         * otherwise resize on the height.
         **/
        if ($resizedImage->width() > $resizedImage->height()) {
            $resizedImage->resize($constraint, null, function ($constraint) {
                $constraint->aspectRatio();
            });
        } else {
            $resizedImage->resize(null, $constraint, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $resizedImage->save(storage_path("app/{$parsedURL['host']}/{$specificFileName}"), 100);
    }

    /**
     * Now, we're going to assess the constraint
     * and return the correct image!
     **/
    if ($constraint == "full") {
        $requestedFile = $file;
    } else {
        //$requestedFile = $filename . "-" . $constraint . "." . $extension;
        $requestedFile = $specificFileName;
    }
    return Storage::disk("local")->response("{$parsedURL['host']}/".$requestedFile, "", $headers);
});
