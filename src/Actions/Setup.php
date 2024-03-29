<?php

declare(strict_types=1);

namespace App\Actions;

use App\GitService;

use function Laravel\Prompts\error;
use function Laravel\Prompts\text;

class Setup
{
  public static function run(): void
  {
    $git_repo = text(
      label: 'Git Repository URL',
      default: 'https://github.com/alexeightsix/nvim-config.git',
      placeholder: 'https://github.com/laravel/framework.git',
      hint: 'The URL of the git repository to clone.',
      validate: fn (string $value) => match (true) {
        empty($value) => 'The URL cannot be empty.',
        default => null
      }
    );

    if (!GitService::repoExists($git_repo)) {
      error('The repository does not exist.');
      exit(1);
    }

    $folder = "git";

    if (is_dir($folder)) {
      error("The folder {$folder} already exists.");
      exit(1);
    }

    GitService::bareClone($git_repo, $folder);

    $myfile = fopen(getcwd() . '/lazywt.json', "w");

    $contents = [
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
