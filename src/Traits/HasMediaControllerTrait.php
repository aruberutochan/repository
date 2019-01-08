<?php

namespace Aruberuto\Repository\Traits;

use Spatie\MediaLibrary\Models\Media;

trait HasMediaControllerTrait
{
    public function download(Media $media, $id)
    {
        $mediaItem = $media->find($id);
        return $mediaItem->getFullUrl();
    }    

}
