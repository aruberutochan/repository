<?php

namespace Aruberuto\Repository\Traits;

use Illuminate\Http\Request;
use Chumper\Zipper\Facades\Zipper;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaStream;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

trait HasMediaControllerTrait
{
    public function download(Media $media, $id)
    {
        $mediaItem = $media->find($id);
        return $mediaItem->getFullUrl();
    }
    
    public function _multiDownload(Media $media, Request $request) {

        $medias = $media->findMany($request->get('ids', []));
        $mediaZip = MediaStream::create($request->get('name', 'my-files') . '.' . $request->get('extension', 'zip'))->addMedia($medias);
        return $mediaZip;

    }

    public function multiDownload(Media $media, Request $request)
    {
        $medias = $media->findMany($request->get('ids', []));
        $mediasToZip = [];
        foreach($medias as $media) {
            $mediasToZip[] = $media->getPath();
        }

        // die(print_r($mediasToZip));
        

        // $headers = ["Content-Type"=>"application/zip"];
        

        $fileName = $request->get('name', 'my-files') . '.' . $request->get('extension', 'zip'); // name of zip
        $filePath = public_path('/download/'.$fileName. Str::random());
        $zip = Zipper::make($filePath) //file path for zip file
                ->add($mediasToZip)->close(); //files to be zipped

        $file = File::get( $filePath);
        $type = File::mimeType( $filePath);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        return $response;

        // $headers = array('Content-Type' => File::mimeType($filePath));
        // return Storage::url($filePath);
        // public_url()

        // return response()->download($filePath, $fileName, $headers);
    }

}
