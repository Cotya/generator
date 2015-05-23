<?php
/**
 *
 *
 *
 *
 */

namespace Cotya\Generator\Magento;

use Cotya\Generator\Definition;

class Module
{


    protected $moduleDefinition;

    public function __construct(Definition\Module $moduleDefinition)
    {
        $this->moduleDefinition = $moduleDefinition;
    }
}
