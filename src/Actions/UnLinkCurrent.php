<?php

declare(strict_types=1);

namespace App\Actions;

class UnLinkCurrent
{
  public static function run(string $link): void
  {
    if (!unlink($link . 'current')) {
      throw new \Exception('Could not remove old symlink');
    }
  }
}
