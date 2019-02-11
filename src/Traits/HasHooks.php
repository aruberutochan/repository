<?php
namespace Aruberuto\Repository\Traits;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait HasHook {
    /**
     * Collection of closure
     *
     * @var Collection
     */
    protected $hookBefore;

     /**
     * Collection of closure
     *
     * @var Collection
     */
    protected $hookAfter;

    /**
     * @var boolean
     */
    protected $skipHookBefore = false;

     /**
     * @var boolean
     */
    protected $skipHookAfter = false;

    public function initHook() {
        $this->hookBefore = new Collection();
        $this->hookAfter = new Collection();
    }

    public function getHookBefore() {
        return $this->hookBefore;
    }

    public function getHookAfter() {
        return $this->hookAfter;
    }

    protected function pushHook($closure, $hookMoment = 'hookAfter') {
        if (!is_callable($closure)) {
            throw new RepositoryException("\$closure must be callable");
        }

        $this->{$hookMoment}->push($closure);

        return $this;
    }

    public function pushHookAfter($closure) {
        return $this->pushHook($closure, 'hookAfter');
    }

    public function pushHookBefore($closure) {
        return $this->pushHook($closure, 'hookBefore');
    }

    public function applyHookBefore($model) {
        foreach($this->getHookBefore() as $function) {
            call_user_func_array($function,[$model]);
        }
        return $this;
    }

    public function applyHookAfter($model) {
        foreach($this->getHookAfter() as $function) {
            call_user_func_array($function,[$model]);
        }
        return $this;
    }

}
