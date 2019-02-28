<?php

namespace Aruberuto\Repository\Traits;

use Spatie\MediaLibrary\Models\Media;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaStream;
trait HasMediaControllerTrait
{
    public function download(Media $media, $id)
    {
        $mediaItem = $media->find($id);
        return $mediaItem->getFullUrl();
    }
    
    public function multiDownload(Media $media, Request $request) {

        $medias = $media->findMany($request->get('ids', []));
        $mediaZip = MediaStream::create($request->get('name', 'my-files') . '.zip')->addMedia($medias);
        return $mediaZip;

    }

}
