<?php
/**
 *
 *
 *
 *
 */

namespace Cotya\Generator\Definition;


class Module
{

    protected $moduleName;
    
    protected $vendorName;
    
    
    public function __construct(
        $moduleName,
        $vendorName
    ) {
        $this->moduleName = $moduleName;
        $this->vendorName = $vendorName;
        
    }
    
    
    public function getName()
    {
        return $this->moduleName;
    }

    public function getVendorName()
    {
        return $this->vendorName;
    }
}
