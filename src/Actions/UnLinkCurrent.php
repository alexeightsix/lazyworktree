<?php

declare(strict_types=1);

namespace App\Actions;

class UnLinkCurrent
{
  public static function run(): void
  {
    if (!unlink(getcwd() . '/current')) {
      throw new \Exception('Could not remove old symlink');
    }
  }
}
