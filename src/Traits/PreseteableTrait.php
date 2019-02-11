<?php
namespace Aruberuto\Repository\Traits;

trait PreseteableTrait
{
    protected $request;

    /**
     * Skip the preset
     *
     * @var boolean
     */
    protected $skipCreatePreset = false;

        /**
     * Skip the preset
     *
     * @var boolean
     */
    protected $skipUpdatePreset = false;

    public function applyStorePreset() {
        // $this->request = $this->request->merge([
        //     'user_id' => Auth::user()->id
        // ]);
        return $this;
    }

    public function applyCreatePreset() {

        return $this->applyStorePreset();
    }

    public function applyUpdatePreset() {

        return $this->applyStorePreset();
    }

}
