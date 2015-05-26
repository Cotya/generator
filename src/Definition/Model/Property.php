<?php
/**
 *
 *
 *
 *
 */

namespace Cotya\Generator\Definition\Model;

class Property
{

    protected $name;
    
    protected $type;

    protected $length;
    
    public function __construct($name, $type, $length = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
    }
    
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function getLength()
    {
        return $this->length;
    }
}
