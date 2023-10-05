<?php

namespace App\Command;

use App\Library\Auth;
use App\Library\DB;
use DI\Container;
use Exception;
use Faker\Factory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * php cli.php app:fillingUsers
 */
class FillingUsers extends BaseCommand
{
    protected static $defaultName = 'app:fillingUsers';
    protected static $defaultDescription = 'Генерация  1,000,000 анкет';
    protected DB $db;
    protected int $count_records = 1000000;

    public function __construct(Container $container)
    {
        parent::__construct();
        $this->db = $container->get(DB::class);
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addArgument('count', InputArgument::OPTIONAL, 'Count Records');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->count_records = intval($input->getArgument('count') ?? $this->count_records);
        //Отчистка БД
        $this->db->exec(/** @lang */ "delete from users");
        $this->db->exec(/** @lang */ "alter sequence users_user_id_seq restart with 1;");
        // Генерация новых анкет
        $base_sql = /** @lang */ "insert into users(login, password, name, surname, lastname, gender, biography, city) values";
        $this->db->logEnable(false);
        try {
            foreach (array_chunk($this->createRecords(), 5000, true) as $new_record_chunk) {
                $this->db->exec($base_sql . implode(',', $new_record_chunk));
            }
        } catch (Exception $e) {
            $output->writeln("Генерация анкет. Error: " . $e->getMessage());
        }

        $output->writeln(sprintf(
            "Генерация анкет (%s). OK",
            number_format($this->count_records,0,'.',' ')
        ));

        return 1;
    }

    private function createRecords()
    {
        $faker = Factory::create('ru_RU');
        $rec = [];
        for ($i = 1; $i <= $this->count_records; $i++) {
            $gender = $faker->randomElement(['male', 'female']);
            $name = explode(' ', $faker->name($gender));
            $rec[] =
                "('user_" . $i . "', " .
                "'" . Auth::getHashPassword('psw' . $i) . "', " .
                "'" . $name[0] . "', " .
                "'" . $name[2] . "', " .
                "'" . $name[1] . "', " .
                "'" . ($gender == 'male' ? 1 : 0) . "', " .
                "'" . implode(', ', $faker->words(15)) . "', " .
                "'" . $faker->city . "')";
        }

        return $rec;
    }

}
