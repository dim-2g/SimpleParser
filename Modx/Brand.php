<?php

namespace Modx;

class Brand extends \Core\Brand
{
    public $modx;

    public function __construct()
    {
        global $modx;
        parent::__construct();
        $this->modx = &$modx;
    }

    public function createOne($name)
    {
        if (!$vendor = $this->modx->getObject('msVendor', array('name' => $name))) {
            $vendor = $this->modx->newObject('msVendor', array('name' => $name));
            $vendor->save();
        }

        return $vendor->get('id');
    }

    public function createAllBrands()
    {
        foreach ($this->items as $brandName => $id) {
            $this->createOne($brandName);
        }
        return true;
    }

    public function findOne($name)
    {
        if ($vendor = $this->modx->getObject('msVendor', array('name' => $name))) {
            return $vendor;
        }
        return false;
    }
}