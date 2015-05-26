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
        $this->createInstallScript($className, $modelIdentifier, $this->modelDefinition->getProperties());
        $this->createXmlConfig($className, $modelIdentifier);
        /*
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

        $constructMethod = $factory->method('_construct');
        $constructMethod->makePublic();
        $parentCallStmt = new Expr\StaticCall(new Node\Name('parent'), '_construct');

        if (strpos($className, '_Collection') !== false) {
            $class->extend('Mage_Core_Model_Resource_Db_Collection_Abstract');
            $idFieldName = $this->idFieldName;
            $class->addStmt($constructMethod->addStmt(new Expr\MethodCall(
                new Expr\Variable('this'),
                '_init',
                [new Node\Scalar\String_($modelIdentifier), new Node\Scalar\String_($idFieldName)]
            )));
        } elseif (strpos($className, '_Resource_') !== false) {
            $class->extend('Mage_Core_Model_Resource_Db_Abstract');
            $idFieldName = $this->idFieldName;
            $class->addStmt($constructMethod->addStmt(new Expr\MethodCall(
                new Expr\Variable('this'),
                '_init',
                [new Node\Scalar\String_($modelIdentifier), new Node\Scalar\String_($idFieldName)]
            )));
        } else {
            $class->extend('Mage_Core_Model_Abstract');
            $class->addStmt($constructMethod->addStmt(new Expr\MethodCall(
                new Expr\Variable('this'),
                '_init',
                [new Node\Scalar\String_($modelIdentifier)]
            )));
        }
        $constructMethod->addStmt($parentCallStmt);
        
        $this->generateClassFile($filePath, $class);
    }


    /**
     * @param $className
     * @param $modelIdentifier
     * @param $properties Definition\Model\Property[]
     */
    protected function createInstallScript($className, $modelIdentifier, $properties)
    {

        $code = <<<'PHP'
$installer = $this;

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$tableName = $installer->getTable('%1$s');

$table = $installer->getConnection()
    ->newTable($tableName)
    %2$s
    ->setComment('%3$s');

$installer->getConnection()->createTable($table);

$installer->endSetup();
PHP;

        $columns = '';
        foreach ($properties as $property) {
            if ($property->getType() === 'id') {
                $columns .= <<<PHP
    ->addColumn(
        '{$property->getName()}',
        Varien_Db_Ddl_Table::TYPE_BIGINT,
        null,
        array(
            'auto_increment' => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'id'
    )
PHP;
            } elseif ($property->getType() === 'reference') {
                $columns .= <<<PHP

    ->addColumn(
        '{$property->getName()}',
        Varien_Db_Ddl_Table::TYPE_BIGINT,
        null,
        array(
            'unsigned'  => true,
            'nullable'  => false,
        ),
        'id'
    )
PHP;
            } else {
                $fieldType = $this->getMagentoDBFieldTypeByProperty($property);
                $lenght = 'null';
                if ('string' == $property->getType()) {
                    $lenght = $property->getLength();
                }

                $columns .= <<<PHP

    ->addColumn('{$property->getName()}',
        {$fieldType},
        {$lenght},
        array(
            'nullable'  => false,
        ),
        '{$property->getName()}'
    )
PHP;
            }

            $columns .= <<<PHP
            
PHP;
        }

        $code = sprintf(
            $code,
            $modelIdentifier,
            $columns,
            $className
        );



        echo "<?php\n\n".$code.PHP_EOL;
    }

    private function getMagentoDBFieldTypeByProperty(Definition\Model\Property $property)
    {
        $typeMapping = [
            'integer' => 'Varien_Db_Ddl_Table::TYPE_INTEGER',
            'string' => 'Varien_Db_Ddl_Table::TYPE_TEXT',
            'text' => 'Varien_Db_Ddl_Table::TYPE_TEXT',
            'datetime' => 'Varien_Db_Ddl_Table::TYPE_DATETIME',
        ];
        if (!isset($typeMapping[(string)$property->getType()])) {
            throw new \Exception('Mapping Type not possible');
        }
        return $typeMapping[(string)$property->getType()];
    }
    
    private function createXmlConfig($className, $modelIdentifier)
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
