<?php

namespace App\Command;

use App\Library\DB;
use DI\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * php cli.php app:createIndex
 */
class DeleteIndex extends BaseCommand
{
    protected static $defaultName = 'app:deleteIndex';
    protected static $defaultDescription = 'Создание индексов';
    protected DB $db;
    protected array $tables = ['users'];

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
        $indexes = $this->db->query_array(sprintf(/** @lang */
                "SELECT tablename,indexname 
            FROM pg_indexes 
            where indexname like 'idx_%%'
            and tablename in ('%s')
            ",
                implode("','", $this->tables)
            )
        );

        if (is_array($indexes)) {
            foreach ($indexes as $idx) {
                try {
                    $this->db->exec("drop index {$idx['indexname']};");
                    $output->writeln(" - ".$idx['tablename'].".".$idx['indexname']);
                } catch (\Exception $e) {
                    $output->writeln("Удаление индекса {$idx['indexname']}. Error: " . $e->getMessage());

                    return 0;
                }
            }
        }

        $output->writeln("Удаление индексов. OK");

        return 1;
    }

}
