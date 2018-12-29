<?php
namespace Aruberuto\Repository\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class AbstractController extends Controller {

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
        } else {
            $request = app()->make(Request::class);
        }
        
        return $request;

    }

    /**
     * If is defined return a Api Resource object
     *
     * @param string $type
     * @param collection $data
     * @return Resource | Model
     */
    protected function maybeMakeResource($type, $data, $status = 200) {
        $resourceName = $type . 'Resource';
        if(isset($this->$resourceName) && $this->$resourceName) {
            $class = $this->$resourceName;
            return (new $class($data))->response()
            ->setStatusCode($status);
        } elseif(is_bool($data)){
            return strval($data);
        } else {
            return $data;
        }
    }

}

