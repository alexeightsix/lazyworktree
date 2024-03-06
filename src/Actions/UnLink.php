<?php

namespace App\Actions;

class UnLink
{
  public static function run(): void
  {
    if (!unlink(getcwd() . '/current')) {
      throw new \Exception('Could not remove old symlink');
    }
  }
}
