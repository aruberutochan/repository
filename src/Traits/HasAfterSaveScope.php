<?php
namespace Aruberuto\Repository\Traits;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait HasAfterSaveScope {
    /**
     * Collection of closure
     *
     * @var Collection
     */
    protected $afterSaveScope;

    /**
     * @var boolean
     */
    protected $skipAfterSave = false;

    public function initAfterSaveScope() {
        $this->afterSaveScope = new Collection();
    }

    public function getAfterSaveScope() {
        return $this->afterSaveScope;
    }

    public function pushAfterSaveScope($closure) {
        if (!is_callable($closure)) {
            throw new RepositoryException("\$closure must be callable");
        }
        $this->afterSaveScope->push($closure);

        return $this;
    }

    public function applyAfterSave($model) {
        foreach($this->getAfterSaveScope() as $function) {
            call_user_func_array($function,[$model]);
        }
    }

}
