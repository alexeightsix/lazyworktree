<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\warning;
use function Laravel\Prompts\info;

class ProcessHook
{
  const HOOK_BEFORE_CHANGE_LOCAL = 'HOOK_BEFORE_CHANGE_LOCAL';
  const HOOK_BEFORE_CHANGE_GLOBAL = 'HOOK_BEFORE_CHANGE_GLOBAL';
  const HOOK_AFTER_CHANGE_LOCAL = 'HOOK_AFTER_CHANGE_LOCAL';
  const HOOK_AFTER_CHANGE_GLOBAL = 'HOOK_AFTER_CHANGE_GLOBAL';
  const HOOK_BEFORE_DELETE_LOCAL = 'HOOK_BEFORE_DELETE_LOCAL';
  const HOOK_BEFORE_DELETE_GLOBAL = 'HOOK_BEFORE_DELETE_GLOBAL';
  const HOOK_AFTER_DELETE_LOCAL = 'HOOK_AFTER_DELETE_LOCAL';
  const HOOK_AFTER_DELETE_GLOBAL = 'HOOK_AFTER_DELETE_GLOBAL';
  const HOOK_BEFORE_ADD_LOCAL = 'HOOK_BEFORE_ADD_LOCAL';
  const HOOK_BEFORE_ADD_GLOBAL = 'HOOK_BEFORE_ADD_GLOBAL';

  public static function run(string $hook, string $cwd): void
  {
    $reflection = new \ReflectionClass(__CLASS__);
    $hooks = $reflection->getConstants();

    if (!array_key_exists(key: $hook, array: $hooks)) {
      throw new \Exception("Hook {$hook} not found");
    }

    $hook_str = $hooks[$hook];

    if (!is_string(value: $hook_str)) {
      throw new \TypeError("Hook {$hook} is not a string");
    }

    $hook = $cwd . "/" . strtolower(string: $hook_str) . ".sh";

    if (!file_exists(filename: $hook)) {
      return;
    }

    if (!is_executable(filename: $hook)) {
      warning(message: "Hook {$hook} is not executable, skipping");
      return;
    }

    info(message: "Running hook: {$hook}");
    shell_exec(command: "/usr/bin/env bash {$hook_str}");
  }
}
