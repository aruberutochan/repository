<?php

namespace Aruberuto\Repository\Traits;

trait AttachAndDetachServiceTrait
{

    public function sync($id, $request, $relation = null)
    {

        if(!$relation && !$request->relation) {
            throw new \Exception("the $request or the url string must contain a relation to attach ");
        }

        $relation = $relation ? $relation : $request->relation ;
        $ids = isset($request->ids) ? $request->ids : [];

        return $this->repository->sync($id, $relation, $ids);

    }


    public function attach($id, $request, $relation = null)
    {

        if(!$relation && !$request->relation) {
            throw new \Exception("the $request or the url string must contain a relation to attach ");
        }

        $relation = $relation ? $relation : $request->relation ;
        $ids = isset($request->ids) ? $request->ids : [];

        return $this->repository->syncWithoutDetaching($id, $relation, $ids);

    }

    public function detach($id, $request, $relation = null)
    {
        if(!$relation && !$request->relation) {
            throw new \Exception("the $request or the url string must contain a relation to attach ");
        }

        $relation = $relation ? $relation : $request->relation ;
        $ids = isset($request->ids) ? $request->ids : [];

        return $this->repository->detach($id, $relation, $ids);

    }

    public function detachAll($id, $request, $relation = null)
    {
        if(!$relation && !$request->relation) {
            throw new \Exception("the $request or the url string must contain a relation to attach ");
        }

        $relation = $relation ? $relation : $request->relation ;

        return $this->repository->sync($id, $relation, []);

    }


}
