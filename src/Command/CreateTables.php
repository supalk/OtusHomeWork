<?php

namespace App\Command;

use App\Library\DB;
use DI\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * php cli.php app:createTables
 */
class CreateTables extends BaseCommand
{
    protected static $defaultName = 'app:createTables';
    protected static $defaultDescription = 'Создание таблиц';
    protected DB $db;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->db = $container->get(DB::class);
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->db->logEnable(false);
        foreach (get_file_list(app_path('Command/sql/table'), 'sql') as $file) {
            $content = file_get_contents($file);
            $content = preg_replace('/(^-{2,}.+)/iD','',$content);
            foreach (explode(';', $content) as $sql) {
                if (strlen($sql) > 2) {
                    try {
                        $this->db->exec($sql . ";");
                    } catch (\Exception $e) {
                        $output->writeln("Создание таблиц. Error: " . $e->getMessage());

                        return 0;
                    }
                }
            }
        }
        $output->writeln("Создание таблиц. OK");

        return 1;
    }
}
