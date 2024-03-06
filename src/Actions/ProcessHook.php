<?php

namespace App\Actions;

use function Laravel\Prompts\warning;
use function Laravel\Prompts\info;

class ProcessHook
{

  const HOOK_BEFORE_CHANGE_LOCAL = 'hook_before_change_local';
  const HOOK_BEFORE_CHANGE_GLOBAL = 'hook_before_change_global';
  const HOOK_AFTER_CHANGE_LOCAL = 'hook_after_change_local';
  const HOOK_AFTER_CHANGE_GLOBAL = 'hook_after_change_global';

  public static function run(string $hook, string $cwd): void
  {
    $hooks = [
      "hook_after_change_local" => "{$cwd}/after.sh",
      "hook_after_change_global" => "{$cwd}/after.sh",
      "hook_before_change_local" => "{$cwd}/before.sh",
      "hook_before_change_global" => "{$cwd}/before.sh"
    ];

    if (!array_key_exists($hook, $hooks)) {
      throw new \Exception("Hook {$hook} not found");
    }

    if (file_exists($hooks[$hook])) {
      if (!is_executable($hooks[$hook])) {
        warning("Hook {$hook} is not executable, skipping");
      } else {
        info("Running hook: {$hook}");
        shell_exec("/usr/bin/env bash {$hooks[$hook]}");
      }
    }
  }
}
