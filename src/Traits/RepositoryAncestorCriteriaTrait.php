<?php
namespace Aruberuto\Repository\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Aruberuto\Repository\Contracts\AncestorCriteriaInterface;

trait RepositoryAncestorCriteriaTrait
{
    /**
     * Model Ancestor
     */
    protected $ancestor;

    /**
     * Skip the ancestor
     *
     * @var boolean
     */
    protected $skipAncestor = false;

    public function initAncestorCriteria() {
        $this->ancestor = new Collection();
    }

   /**
     * Push Ancestor for filter the query
     *
     * @param $ancestor
     *
     * @return $this
     * @throws \Aruberuto\Repository\Exceptions\RepositoryException
     */
    public function pushAncestor($ancestor)
    {
        if (is_string($ancestor)) {
            $ancestor = new $ancestor;
        }
        if (!$ancestor instanceof AncestorCriteriaInterface) {
            throw new RepositoryException("Class " . get_class($ancestor) . " must be an instance of Aruberuto\\Repository\\Contracts\\AncestorCriteriaInterface");
        }
        $this->ancestor->push($ancestor);

        return $this;
    }

    /**
     * Pop Ancestor
     *
     * @param $ancestor
     *
     * @return $this
     */
    public function popAncestor($ancestor)
    {
        $this->ancestor = $this->ancestor->reject(function ($item) use ($ancestor) {
            if (is_object($item) && is_string($ancestor)) {
                return get_class($item) === $ancestor;
            }

            if (is_string($item) && is_object($ancestor)) {
                return $item === get_class($ancestor);
            }

            return get_class($item) === get_class($ancestor);
        });

        return $this;
    }

    /**
     * Get Collection of Ancestor
     *
     * @return Collection
     */
    public function getAncestor()
    {
        return $this->ancestor;
    }

    /**
     * Find data by Ancestor
     *
     * @param AncestorCriteriaInterface $ancestor
     *
     * @return mixed
     */
    public function getByAncestor(AncestorCriteriaInterface $ancestor)
    {
        $this->model = $ancestor->apply($this->model, $this);
        $results = $this->model->get();
        $this->resetModel();

        return $this->parserResult($results);
    }

    /**
     * Skip Ancestor
     *
     * @param bool $status
     *
     * @return $this
     */
    public function skipAncestor($status = true)
    {
        $this->skipAncestor = $status;

        return $this;
    }

    /**
     * Reset all Ancestors
     *
     * @return $this
     */
    public function resetAncestor()
    {
        $this->ancestor = new Collection();

        return $this;
    }



        /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyAncestor()
    {

        if ($this->skipAncestor === true) {
            return $this;
        }

        $ancestor = $this->getAncestor();

        if ($ancestor) {
            foreach ($ancestor as $a) {
                if ($a instanceof AncestorCriteriaInterface) {
                    $this->model = $a->apply($this->model, $this);
                }
            }
        }

        return $this;
    }

}