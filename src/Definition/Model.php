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

    /** @var  string */
    protected $classname;
    
    /** @var  Model\Property[] */
    protected $properties;

    /**
     * @param $classname
     * @param $properties Model\Property[]
     */
    public function __construct($classname, $properties)
    {
        $this->classname = $classname;
        $this->properties = $properties;
    }
    
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * @return Model\Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
