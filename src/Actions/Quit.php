<?php

namespace App\Actions;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class Quit
{
  public const MENU_NAME = 'Quit';

  public static function run(): void
  {
    exit(0);
  }
}
