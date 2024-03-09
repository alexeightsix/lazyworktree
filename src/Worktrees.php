<?php

declare(strict_types=1);

namespace App;

class Worktrees
{
  private array $worktrees;

  public function __construct()
  {
    $this->worktrees = [];
  }

  public function exists(string $baseName): bool
  {
    foreach ($this->worktrees as $worktree) {
      if ($worktree->baseName === $baseName) {
        return true;
      }
    }
    return false;
  }

  public function where(string $key, string $value): ?Worktree
  {
    foreach ($this->worktrees as $worktree) {
      if ($worktree->{$key} === $value) {
        return $worktree;
      }
    }
    return null;
  }

  public function addWorktree(Worktree $worktree): void
  {
    if ($worktree->isBare()) {
      return;
    }

    $this->worktrees[] = $worktree;
  }

  public function isEmpty(): bool
  {
    return empty(count($this->worktrees));
  }

  public function get(): array
  {
    return $this->worktrees;
  }

  public function getJson(): string
  {
    $worktrees = [];

    foreach ($this->worktrees as $worktree) {
      $worktrees[] = $worktree->get();
    }

    return json_encode($worktrees, JSON_THROW_ON_ERROR);
  }
}
