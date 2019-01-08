<?php
namespace Aruberuto\Repository\Traits;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait HasAfterSaveActions {
    /**
     * Collection of closure
     *
     * @var Collection
     */
    protected $afterSaveActions;

    /**
     * @var boolean
     */
    protected $skipAfterSave = false;

    public function initAfterSaveActions() {
        $this->afterSaveActions = new Collection();
    }

    public function getAfterSaveActions() {
        return $this->afterSaveActions;
    }

    public function pushAfterSaveAction($closure) {
        if (!is_callable($closure)) {
            throw new RepositoryException("\$closure must be callable");
        }
        $this->afterSaveActions->push($closure);

        return $this;
    }

    public function applyAfterSave($model) {
        foreach($this->getAfterSaveActions() as $function) {
            call_user_func_array($function,[$model]);
        }
        return $this;
    }

}
