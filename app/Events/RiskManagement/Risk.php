<?php

namespace App\Events\RiskManagement;

class Risk
{
    /**
     * @var string
     */
    public $code;

    /**
     * @var string
     */
    public $level;

    /**
     * @var array
     */
    public $objects;

    public function __construct(string $code, string $level, array $objects)
    {
        $this->code    = $code;
        $this->level   = $level;
        $this->objects = $objects;
    }
}

