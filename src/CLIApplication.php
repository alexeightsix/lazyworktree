<?php

declare(strict_types=1);

namespace App;


use App\Actions\Add;
use App\Actions\Change;
use App\Actions\Delete;
use App\Actions\Link;
use App\Actions\Quit;
use App\Actions\Setup;

use App\ApiOptions;

use Exception;

use function Laravel\Prompts\error;
use function Laravel\Prompts\warning;
use function laravel\Prompts\select;

class CLIApplication
{

  private array $options;
  private array $menuItems = [];

  private const OPT_INIT = 'init';
  private const OPT_SWITCH = 'switch';

  public function __construct(array $options)
  {
    $this->options = $options;
  }

  private function hasOption(string $option): bool
  {
    return in_array($option, $this->options);
  }

  private function registerMenuItem(mixed $action): void
  {
    $this->menuItems[$action] = $action::MENU_NAME; // @phpstan-ignore-line
  }

  private function isValidApiAction(string $api_action): bool
  {
    return in_array(
      $api_action,
      [
        ApiOptions::switch->name,
        ApiOptions::list->name,
        ApiOptions::health->name,
      ]
    );
  }

  private function handleApi(): void
  {
    $api_action = (string) $this->options[2] ?? null;

    if (!$this->isValidApiAction($api_action)) {
      error('Invalid API action.');
      exit(1);
    }

    try {
      $git_root = Helpers::findGitFolder();
      $worktrees = GitService::getWorktrees(git_path: $git_root);
    } catch (Exception) {
      $invalid = true;
    }

    if ($api_action === ApiOptions::health->name) {
      if (isset($invalid)) {
        exit(1);
      } else {
        exit(0);
      }
    } elseif ($api_action === ApiOptions::list->name) {
      echo $worktrees->getJson();
      exit(0);
    } elseif ($api_action === ApiOptions::health->name) {
      $option = $this->options[3] ?? null;

      if ($option === null) {
        error('No worktree specified.');
        exit(1);
      }

      $worktree = $worktrees->where("baseName", $option);

      if (!$worktree) {
        error('Worktree not found.');
        exit(1);
      }

      Link::run($worktree);
      exit(0);
    }
  }

  public function run(): int
  {
    try {
      Helpers::getRoot();
      $initalized = true;
    } catch (Exception) {
    }

    $init = $this->hasOption(self::OPT_INIT);

    if ($this->hasOption("api")) {
      $this->handleApi();
      exit(0);
    }

    if (!$init && !isset($initalized)) {
      error('No lazywt.json file found. Please run `lazywt init` in the root of your project.');
      exit(1);
    }

    foreach (["add", "switch", "delete"] as $option) {
      if ($this->hasOption($option)) {
        match ($option) {
          "add" => Add::run(),
          "switch" => Change::run(),
          "delete" => Delete::run(),
        };
        exit(0);
      }
    }

    if ($this->hasOption("add") || $this->hasOption("switch")) {
      Add::run();
      exit(0);
    }

    if ($init && !$initalized) {
      Setup::run();
    } else if ($init && $initalized) {
      error('A lazywt.json file already exists. Please remove it before running `lazywt init`.');
      exit(1);
    }

    $git_root = Helpers::findGitFolder();

    $worktrees = GitService::getWorktrees($git_root);

    if ($this->hasOption(self::OPT_SWITCH)) {
      if ($worktrees->isEmpty()) {
        error('No worktrees to switch to. Add a worktree first.');
        exit(1);
      }

      Change::run();
      exit(0);
    }

    if ($worktrees->isEmpty()) {
      warning('Add your first worktree.');
      Add::run();
      exit(0);
    }

    $this->registerMenuItem(Add::class);
    $this->registerMenuItem(Change::class);
    $this->registerMenuItem(Delete::class);
    $this->registerMenuItem(Quit::class);

    $option = select(label: 'Select Action', options: $this->menuItems);

    try {
      $option::run(); // @phpstan-ignore-line
      exit(0);
    } catch (Exception $e) {
      error($e->getMessage());
      exit(1);
    }
  }
}
