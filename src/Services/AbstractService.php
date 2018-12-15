<?php
namespace Aruberuto\Repository\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Aruberuto\Repository\Criteria\RequestCriteria;
use Aruberuto\Repository\Contracts\RepositoryInterface;
use Exception;
abstract class AbstractService {

    /**
     * @var Repository
     */
    protected $repository;

    /**
     * @var Entity Relations Array
     */
    protected $relations = [];

    protected $storeAncestor = null;
    protected $getterCriteria = [RequestCriteria::class];
    protected $skipCriteria = false;
    protected $skipAncestor = false;
    protected $skipRelations = false;

    /**
     * @var Entity Relations Array
     */
    protected $createRelations = true;

    /**
     * GameServiceService constructor.
     *
     * @param GameRepository $repository
     * @param GameValidator $validator
     */
    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all games
     *
     * @return Collection
     */
    public function getAll()
    {
        if(!$this->skipCriteria) {
            foreach($this->getterCriteria as $criteria) {
                $this->repository = $this->repository->pushCriteria(app($criteria));
            }
        }
        if(!$this->skipRelations) {
            $this->repository = $this->repository->with($this->relations);
        }

        return $this->repository->all();
    }

     /**
     * Paginate all entities
     *
     * @return Collection
     */
    public function paginate()
    {
        if(!$this->skipCriteria) {
            foreach($this->getterCriteria as $criteria) {
                $this->repository = $this->repository->pushCriteria(app($criteria));
            }
        }
        if(!$this->skipRelations) {
            $this->repository = $this->repository->with($this->relations);
        }

        return $this->repository->paginate();
    }

    public function create($request)
    {
        if($this->skipAncestor) {
            $this->repository = $this->repository->skipAncestor();
        } elseif($this->storeAncestor) {
            foreach($this->storeAncestor as $ancestor) {
                $this->repository = $this->repository->pushAncestor(app($ancestor));
            }
        }

        $return = $this->repository->create($request->all());
        if($this->createRelations) {
            $return = $this->updateOrCreateRelationships($return, $request);
        }
        return $return;

    }

    public function skipCreateRelations($bool = true ) {
        $this->createRelations = !$bool;
        return $this;
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
        $return = $this->repository->update($request->all(), $id);
        if($this->createRelations) {
            $return = $this->updateOrCreateRelationships($return, $request);
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
        foreach($this->relations as $relation) {
            $model->$relation()->delete();

        }
        return $model;
    }

    public function destroy($id)
    {
        return $this->repository->delete($id);

    }

}
