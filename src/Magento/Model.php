<?php
/**
 *
 *
 *
 *
 */

namespace Cotya\Generator\Magento;

use Cotya\Generator\Definition;
use \PhpParser\Node;
use \PhpParser\Node\Expr;

class Model
{
    protected $modelDefinition;
    
    protected $moduleDefinition;
    
    protected $idFieldName;

    public function __construct(Definition\Model $definition, Definition\Module $moduleDefinition, $idFieldName)
    {
        $this->modelDefinition = $definition;
        $this->moduleDefinition = $moduleDefinition;
        $this->idFieldName = $idFieldName;
    }
    
    public function generate($buildpath)
    {
        
        $className = $this->modelDefinition->getClassname();
        $modelIdentifier =  $this->getModelIdentifier($className);
        $this->createModelFile($className, $buildpath);
        $this->createGeneratedModelFile($className, $modelIdentifier, $buildpath);
        $ressouceClassName = $this->getResourceModelClassName($className);
        $this->createModelFile($ressouceClassName, $buildpath);
        $this->createGeneratedModelFile($ressouceClassName, $modelIdentifier, $buildpath);
        $collectionClassName = $this->getCollectionClassName($className);
        $this->createModelFile($collectionClassName, $buildpath);
        $this->createGeneratedModelFile($collectionClassName, $modelIdentifier, $buildpath);
        /*
        $this->createInstallScript($className, $modelIdentifier, $entity);
        $this->createXmlConfig($className, $modelIdentifier, $entity);
        */
        return null;
    }
    
    protected function getModelIdentifier($className)
    {
        $parts = explode('_', $className);
        $moduleIdentifier = [];
        $moduleIdentifier[] = array_shift($parts);
        $moduleIdentifier[] = array_shift($parts);

        array_shift($parts); // remove "model" part from className

        return strtolower(implode('_', $moduleIdentifier).'/'.implode('_', $parts));
    }

    protected function getGeneratedClassName($className)
    {
        return $className.'Generated';
    }

    protected function getCollectionClassName($className)
    {
        return $this->getResourceModelClassName($className).'_Collection';
    }

    protected function getResourceModelClassName($className)
    {
        $parts = explode('_', $className);
        $prefix = [];
        $prefix[] = array_shift($parts);
        $prefix[] = array_shift($parts);
        $prefix[] = array_shift($parts);
        $prefix[] = 'Resource';

        return implode('_', array_merge($prefix, $parts));
    }

    protected function getClassFileLocationByName($className, $basedir)
    {
        $path = str_replace('_', DIRECTORY_SEPARATOR, $className);
        $path = $basedir.'/local/'.$path.'.php'; // Mage::getBaseDir('code')
        return $path;
    }





    protected function generateClassFile($filePath, \PhpParser\Builder\Class_ $class)
    {
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }
        $stmts = array($class->getNode());
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard();
        $code = $prettyPrinter->prettyPrintFile($stmts);
        file_put_contents($filePath, $code.PHP_EOL);
    }

    protected function createModelFile($className, $basedir)
    {
        $filePath = $this->getClassFileLocationByName($className, $basedir);
        if(file_exists($filePath)){
            //return;
        }
        
        $factory = new \PhpParser\BuilderFactory;
        $class = $factory->class($className);
        $class->extend($this->getGeneratedClassName($className));

        $this->generateClassFile($filePath, $class);
    }

    protected function createGeneratedModelFile($className, $modelIdentifier, $basedir)
    {
        $generatedClassName = $this->getGeneratedClassName($className);
        $filePath = $this->getClassFileLocationByName($generatedClassName, $basedir);

        $factory = new \PhpParser\BuilderFactory;
        $class = $factory->class($generatedClassName);
        $class->extend($this->getGeneratedClassName($className));
        
        //$code = new Zend_CodeGenerator_Php_Class();
        
        $parentCallStmt = new Expr\StaticCall(new Node\Name('parent'), '_construct');

        
        if (strpos($className, '_Collection') !== false) {
            $class->extend('Mage_Core_Model_Resource_Db_Collection_Abstract');
            $idFieldName = $this->idFieldName;

            $class->addStmt($factory->method('_construct')
                ->addStmt(new Expr\MethodCall(
                    new Expr\Variable('this'),
                    '_init',
                    [new Node\Scalar\String_($modelIdentifier), new Node\Scalar\String_($idFieldName)]
                ))
                ->addStmt($parentCallStmt));
        } elseif (strpos($className, '_Resource_') !== false) {
            $class->extend('Mage_Core_Model_Resource_Db_Abstract');
            $idFieldName = $this->idFieldName;

            $class->addStmt($factory->method('_construct')
                ->addStmt(new Expr\MethodCall(
                    new Expr\Variable('this'),
                    '_init',
                    [new Node\Scalar\String_($modelIdentifier), new Node\Scalar\String_($idFieldName)]
                ))
                ->addStmt($parentCallStmt));
        } else {
            $class->extend('Mage_Core_Model_Abstract');

            $class->addStmt($factory->method('_construct')
                ->addStmt(new Expr\MethodCall(
                    new Expr\Variable('this'),
                    '_init',
                    [new Node\Scalar\String_($modelIdentifier)]
                ))
                ->addStmt($parentCallStmt));
        }

        $this->generateClassFile($filePath, $class);
    }

    private function createXmlConfig($className, $modelIdentifier, SimpleXMLElement $entity)
    {
        $parts = explode('/', $modelIdentifier);
        $tableName = str_replace('_Model', '', $className);
        $xml = <<<XML
<global>
    <models>
        <{$parts[0]}_resource>
            <entities>
                <{$parts[1]}>
                    <table>{$tableName}</table>
                </{$parts[1]}>
            </entities>
        </{$parts[0]}_resource>
    </models>
</global>
XML;

        echo PHP_EOL.$xml.PHP_EOL;
    }
}
