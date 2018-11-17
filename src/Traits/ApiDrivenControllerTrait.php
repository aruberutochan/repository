<?php
namespace Aruberuto\Repository\Traits;

use Illuminate\Http\Request;

trait ApiDrivenControllerTrait {

    protected $storeRequest = Request::class;
    protected $indexRequest = Request::class;
    protected $showRequest = Request::class;
    protected $updateRequest = Request::class;
    protected $destroyRequest = Request::class;

    protected $collectionResource = null;
    protected $singleResource = null;
    protected $deleteResource = null;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $request = $this->makeRequest('index');
        $result = $this->service->getAll();
        return $this->maybeMakeResource('collection', $result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  $request
     *
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function store()
    {
        $request = $this->makeRequest('store');
        $model = $this->service->create($request);

        if(is_a($model, \Exception::class)) {

            return $this->getErrorResource($model);

        } else {

            return $this->maybeMakeResource('single', $model);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $request = $this->makeRequest('show');
        $model = $this->service->get($id);
        return $this->maybeMakeResource('single', $model);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  PostUpdateRequest $request
     * @param  string            $id
     *
     * @return Response
     *
     */
    public function update($id)
    {
        $request = $this->makeRequest('update');
        $model = $this->service->update($request, $id);

        if(is_a($model, \Exception::class)) {

            return $this->getErrorResource($model);

        } else {

            return $this->maybeMakeResource('single', $model);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $request = $this->makeRequest('destroy');
        $deleted = $this->service->destroy($id);
        return $this->getDeleteResource('delete', $deleted);

    }

    /**
     * Create a FormRequest declared in property type
     *
     * @param string $type
     * @return Illuminate\Http\Request | void
     */
    protected function makeRequest($type) {

        if(isset($this->{$type . 'Request'}) && $this->{$type . 'Request'}) {
            $request = app()->make($this->{$type . 'Request'});

            if (!$request instanceof Request) {
                throw new \Exception("Class {$this->{$type . 'Request'}} must be an instance of Illuminate\\Http\\Request");
            }
            return $request;
        }

    }

    /**
     * If is defined return a Api Resource object
     *
     * @param string $type
     * @param collection $data
     * @return Resource | Model
     */
    protected function maybeMakeResource($type, $data) {
        $resourceName = $type . 'Resource';
        if(isset($this->$resourceName) && $this->$resourceName) {
            $class = $this->$resourceName;
            return new $class($data);
        } else {
            return $data;
        }
    }
}
