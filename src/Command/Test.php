<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * php cli.php app:test
 * Class SyncRoles
 * @package App\Command
 */
class Test extends SymfonyCommand
{
    protected static $defaultName = 'app:test';
    protected static $defaultDescription = 'Тестирование....';


    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->setHelp(self::$defaultDescription)
            ->addArgument('param1', InputArgument::OPTIONAL, 'Параметр 1')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        return 1;
    }

}
