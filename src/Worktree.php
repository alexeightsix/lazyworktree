<?php

declare(strict_types=1);

namespace App;

class Worktree
{
  protected array $worktree;

  public ?bool $bare;
  public ?string $baseName;
  public ?string $branch;
  public ?string $head;
  public ?string $path;

  public function __construct(array $worktree)
  {
    $this->bare = false;
    $this->baseName = null;
    $this->branch = null;
    $this->head = null;
    $this->worktree = $worktree;

    if ($worktree[1] === "bare") {
      $this->bare = true;
    }

    $path = explode(" ", $worktree[0]);

    if ($path[0] === "worktree") {
      $this->path = $path[1];
    }

    if ($this->bare) {
      return;
    }

    $this->setHead();
    $this->setBranch();
    $this->setBaseName();
  }

  public function __get(string $name): mixed
  {
    if ($name === "worktree") {
      throw new \Exception("Cannot access worktree property.");
    }

    if (property_exists($this, $name)) {
      return $this->$name;
    }

    throw new \Exception("Property {$name} does not exist.");
  }

  public function __set(string $name, mixed $value): void
  {
    throw new \Exception("Cannot set value of {$name}");
  }

  private function setHead(): void
  {
    $head = $this->worktree[1];
    $head = explode("HEAD", $head);
    $head = array_filter($head);
    $head = array_values($head);
    $head = array_map("trim", $head);
    $this->head = $head[0];
  }

  public function isBare(): bool
  {
    return $this->bare !== null && $this->bare === true;
  }

  private function setBranch(): void
  {
    $branch = $this->worktree[2];
    $branch = explode(" ", $branch);
    $this->branch = $branch[1];
  }

  private function setBaseName(): void
  {
    if ($this->path === null) {
      throw new \Exception("Path is not set.");
    }

    $this->baseName = basename($this->path);
  }

  public function get(): array | string | null
  {
    return [
      'baseName' => $this->baseName,
      'branch' => $this->branch,
      'head' => $this->head,
      'path' => $this->path
    ];
  }
}
