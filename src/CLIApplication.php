<?php

declare(strict_types=1);

namespace App;

use App\Config;

use App\Actions\Add;
use App\Actions\Change;
use App\Actions\Delete;
use App\Actions\Quit;
use App\Actions\Setup;

use Exception;

use function Laravel\Prompts\error;
use function Laravel\Prompts\warning;
use function laravel\Prompts\select;

class CLIApplication
{
  private array $options;
  private array $menuItems = [];

  private const OPT_INIT = 'init';

  public function __construct(array $options)
  {
    $this->options = $options;
  }

  private function hasOption(string $option): bool
  {
    return in_array($option, $this->options);
  }

  private function isInitalized(): bool
  {
    return file_exists(getcwd() . '/lazywt.json');
  }

  private function registerMenuItem(mixed $action): void
  {
    $this->menuItems[$action] = $action::MENU_NAME;
  }

  public function run() : int
  {
    $initalized = $this->isInitalized();
    $init = $this->hasOption(self::OPT_INIT);

    if (!$init && !$initalized) {
      error('No lazywt.json file found. Please run `lazywt init` in the root of your project.');
      exit(1);
    } else if ($init && !$initalized) {
      Setup::run();
    } else if ($init && $initalized) {
      error('A lazywt.json file already exists. Please remove it before running `lazywt init`.');
      exit(1);
    }

    $git_root = Config::get('git_folder');

    if (!is_dir($git_root)) {
      error('The git folder does not exist.');
      exit(1);
    }

    $worktrees = GitService::getWorktrees($git_root);

    if (empty($worktrees)) {
      warning('Add your first worktree.');
      Add::run();
      exit(0);
    }

    $this->registerMenuItem(Add::class);

    if (!empty($worktrees)) {
      $this->registerMenuItem(Change::class);
      $this->registerMenuItem(Delete::class);
    }

    $this->registerMenuItem(Quit::class);

    if ($this->hasOption('switch')) {
      if (empty($worktrees)) {
        error('No worktrees to switch to. Add a worktree first.');
        exit(1);
      }

      Change::run();
      exit(0);
    }

    $option = select('Select Action', $this->menuItems);

    try {
      $option::run();
      exit(0);
    } catch (Exception $e) {
      error($e->getMessage());
      exit(1);
    }
  }
}
