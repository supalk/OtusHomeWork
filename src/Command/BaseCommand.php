<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

abstract class BaseCommand extends SymfonyCommand
{
    /** @var string Краткое описание команды */
    protected $title = '';
    /** @var string Кодовый идентификатор команды */
    protected $key;


}
