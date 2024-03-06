<?php

declare(strict_types=1);

namespace App\Actions;

class Quit
{
  public const MENU_NAME = 'Quit';

  public static function run(): void
  {
    exit(0);
  }
}
