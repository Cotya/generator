<?php
/**
 *
 *
 *
 *
 */

namespace Cotya\Generator\Definition;


class Model
{
    protected $classname;

    public function __construct($classname)
    {
        $this->classname = $classname;
    }
    
    public function getClassname()
    {
        return $this->classname;
    }
}
