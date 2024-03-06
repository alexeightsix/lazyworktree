<?php

namespace App\Actions;

use function Laravel\Prompts\text;
use function Laravel\Prompts\spin;

class Setup
{
  public static function run() : void
  {
    $git_repo = text(
      label: 'Git Repository URL',
      placeholder: 'https://github.com/laravel/framework.git',
      hint: 'The URL of the git repository to clone.',
      default: 'https://github.com/laravel/framework.git',
      validate: fn (string $value) => match (true) {
        empty($value) => 'The URL cannot be empty.',
        default => null
      }
    );

    $folder = text(
      label: 'Folder Name',
      placeholder: 'laravel-framework',
      hint: 'The folder to clone the bare git repository into.',
      default: 'laravel-framework',
      validate: fn (string $value) => match (true) {
        empty($value) => 'The folder cannot be empty.',
        !preg_match('/^([a-zA-Z0-9_-])+$/', $value) => 'The folder name must be valid.',
        is_dir($value) => 'The folder already exists.',
        default => null
      }
    );

    spin(
      fn () => shell_exec("git clone {$git_repo} {$folder} --bare > /dev/null 2>&1"),
      'Cloning Repository'
    );

    $myfile = fopen(getcwd() . '/lazywt.json', "w");

    $contents = [
      'created_at' => date('Y-m-d H:i:s'),
      'git_folder' => $folder,
      'git_repo' => $git_repo
    ];

    $json = json_encode($contents, JSON_PRETTY_PRINT);

    if (!is_resource($myfile) || is_string($json) === false) {
      throw new \Exception('Invalid resource or JSON string.');
    }

    fwrite($myfile, (string) $json);
    fclose($myfile);
  }
}
