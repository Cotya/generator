<?php
/**
 *
 *
 *
 *
 */

namespace Cotya\Generator\Console;

use Cotya\Generator\Definition\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MagentoModuleCreate extends Command
{

    protected function configure()
    {
        $this
            ->setName('magento:module:create')
            ->setDescription('creates a basic skeleton for travis CI')
            ->addArgument(
                'moduledefinition',
                InputArgument::REQUIRED,
                'The module definition file'
            )
            ->addArgument(
                'buildpath',
                InputArgument::REQUIRED,
                'The module definition file'
            )
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $definitionParser = new Parser($input->getArgument('moduledefinition'));
        foreach($definitionParser->getMagentoModelDefinitions() as $model){
            var_dump($model->generate($input->getArgument('buildpath')));
        }
    }
}
