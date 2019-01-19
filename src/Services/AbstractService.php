<?php
namespace Aruberuto\Repository\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Aruberuto\Repository\Traits\PreseteableTrait;
use Aruberuto\Repository\Criteria\RequestCriteria;
use Aruberuto\Repository\Contracts\RepositoryInterface;

abstract class AbstractService {
    use PreseteableTrait;
    /**
     * @var Repository
     */
    protected $repository;

    protected $request;


    /**
     * @var Entity Relations Array
     */
    protected $relations = [];
    protected $deleteRelations = [];

    protected $storeAncestor = null;
    protected $getterCriteria = [RequestCriteria::class];
    protected $skipCriteria = false;
    protected $skipAncestor = false;
    protected $skipRelations = false;

    protected $skipAfterCreateActions = false;
    protected $skipAfterUpdateActions = false;

    /**
     * @var Entity Relations Array
     */
    protected $createRelations = true;

        /**
     * @var Entity Relations Array
     */
    protected $syncRelations = true;


    /**
     * GameServiceService constructor.
     *
     * @param GameRepository $repository
     * @param GameValidator $validator
     */
    public function __construct($repository)
    {
        $this->repository = $repository;
        $this->request = app()->make(Request::class);
    }

    /**
     * List entities
     *
     * @return Collection
     */

    public function list($method = 'all') {
        if(!$this->skipCriteria) {
            foreach($this->getterCriteria as $criteria) {
                $this->repository = $this->repository->pushCriteria(app($criteria));
            }
        }
        if(!$this->skipRelations) {
            $this->repository = $this->repository->with($this->relations);
        }

        return $this->repository->{$method}();
    }

    /**
     * Get all games
     *
     * @return Collection
     */
    public function getAll()
    {
        return $this->list('all');
    }

     /**
     * Paginate all entities
     *
     * @return Collection
     */
    public function paginate()
    {
        return $this->list('paginate');
    }

    public function applyAfterCreateActions($model) {
        $this->applyAfterSaveActions($model);
    }

    public function applyAfterUpdateActions($model) {
        $this->applyAfterSaveActions($model);
    }

    public function applyAfterSaveActions($model) {

    }

    public function create($request)
    {
        $this->request = $request;

        if($this->skipAncestor) {
            $this->repository = $this->repository->skipAncestor();
        } elseif($this->storeAncestor) {
            foreach($this->storeAncestor as $ancestor) {
                $this->repository = $this->repository->pushAncestor(app($ancestor));
            }
        }

        if(!$this->skipCreatePreset) {
            $this->applyCreatePreset();
        }

        if(!$this->skipAfterCreateActions) {
            $this->repository->pushAfterSaveAction([&$this, 'applyAfterCreateActions']);
        }

        $return = $this->repository->create($this->request->all());

        if($this->createRelations) {
            $return = $this->updateOrCreateRelationships($return, $this->request);
        }
        if($this->syncRelations) {
            $return = $this->syncRelationships($return, $this->request);
        }
        return $return;

    }

    public function skipCreateRelations($bool = true ) {
        $this->createRelations = !$bool;
        return $this;
    }

    public function syncRelationships($model, $request) {

        foreach($this->relations as $relation) {
            // dd($request);

            if($request->has('sync') && isset($request->sync[$relation])) {
                $this->repository->sync($model->id, $relation, $request->sync[$relation] );
            }
        }

        $return = $model->load($this->relations);
        return $return;

    }

    public function createRelationships($model, $request) {

        foreach($this->relations as $relation) {
            if(isset($request->$relation)) {
                if(is_array($request->$relation)) {
                    $toSave = [];
                    foreach ($request->$relation as $entityAtributtes) {
                        if(!is_array($entityAtributtes)) {
                            $toSave[] = $request->$relation;
                            break;
                        }
                        $toSave[] = $entityAtributtes;
                    }
                    $model->$relation()->createMany($toSave);
                }

            }
        }

        $return = $model->load($this->relations);
        return $return;

    }

    public function get($id)
    {
        if(!$this->skipCriteria) {
            foreach($this->getterCriteria as $criteria) {
                $this->repository = $this->repository->pushCriteria(app($criteria));
            }
        }
        if(!$this->skipRelations) {
            $this->repository = $this->repository->with($this->relations);
        }
        return $this->repository->find($id);
    }

    public function update($request, $id)
    {
        $this->request = $request;

        if(!$this->skipUpdatePreset) {
            $this->applyUpdatePreset();
        }

        $return = $this->repository->update($this->request->all(), $id);
        if($this->createRelations) {
            $return = $this->updateOrCreateRelationships($return, $this->request);
        }


        if(!$this->skipAfterUpdateActions) {
            $this->repository->pushAfterSaveAction($this->afterUpdateActions);
        }

        if($this->syncRelations) {
            $return = $this->syncRelationships($return, $this->request);
        }
        return $return;

    }

    public function updateOrCreateRelationships($model, $request) {

        foreach($this->relations as $relation) {
            // dd($request);

            if(isset($request->$relation)) {
                $rel = $model->$relation();
                $primaryKey = 'id';
                if(is_array($request->$relation)) {
                    $toUpdate = [];
                    $toCreate = [];
                    foreach ($request->$relation as $entityAtributtes) {
                        if(!is_array($entityAtributtes)) {
                            if(isset($request->$relation[$primaryKey])) {
                                $toUpdate[] = $request->$relation;
                            } else {
                                $toCreate[] = $request->$relation;
                            }
                            break;
                        }
                        if(isset($entityAtributtes[$primaryKey])) {
                            $toUpdate[] = $entityAtributtes;
                        } else {
                            $toCreate[] = $entityAtributtes;
                        }
                    }
                    foreach($toUpdate as $attributes) {
                        $key = $attributes[$primaryKey];
                        unset($attributes[$primaryKey]);
                        $rel = $model->$relation()->find($key);

                        if($rel) {
                            $rel->update($attributes);
                        } else {
                            throw new Exception('Wrong primary key for relation "' . $relation . '"');
                        }

                    }
                    $model->$relation()->createMany($toCreate);
                }

            }
        }

        $return = $model->load($this->relations);
        return $return;

    }

    public function deleteRelationships($model) {
        foreach($this->deleteRelations as $relation) {
            $model->$relation()->delete();

        }
        return $model;
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);
    }

}
