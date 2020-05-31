<?php

namespace frdl\mount;

use frdl\mount\Driver;

class Repository {

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public static function getOptions() :array
    {
        return $this->driver::getOptions();
    }
}
