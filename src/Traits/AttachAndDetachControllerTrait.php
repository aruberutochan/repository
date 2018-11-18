<?php

namespace Aruberuto\Repository\Traits;

use Illuminate\Http\Request;

trait AttachAndDetachControllerTrait
{

    protected $syncRequest = Request::class;
    protected $attachRequest = Request::class;
    protected $detachRequest = Request::class;
    protected $detachAllRequest = Request::class;

    public function sync($id, $relation = null)
    {
        $request = $this->makeRequest('sync');
        return $this->service->sync($id, $request, $relation);

    }


    public function attach($id, $request, $relation = null)
    {
        $request = $this->makeRequest('attach');
        return $this->service->attach($id, $request, $relation);

    }

    public function detach($id, $request, $relation = null)
    {
        $request = $this->makeRequest('detach');
        return $this->service->detach($id, $request, $relation);

    }

    public function detachAll($id, $request, $relation = null)
    {
        $request = $this->makeRequest('detachAll');
        return $this->service->detachAll($id, $request, $relation);

    }


}
