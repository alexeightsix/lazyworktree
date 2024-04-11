<?php

use App\Helpers;
use CzProject\GitPhp\Git;

afterEach(function () {
  try {
    Helpers::rmDirRecursive('/tmp/tests_lw');
  } catch (Exception) {
  }
});


function createTmpDir(bool $git = false, array $withBranches = ["master"]): array
{
  $directory = '/tmp/tests_lw/' . str_shuffle(uniqid());
  mkdir(directory: $directory, recursive: true);

  if (!is_dir($directory)) {
    throw new \Exception('Could not create directory');
  }

  if ($git) {
    $git = new Git;
    $repo = $git->open($directory);
    $git->init($repo->getRepositoryPath());

    $filename = $repo->getRepositoryPath() . '/readme.txt';
    file_put_contents($filename, "Lorem ipsum dolor sit amet");

    $repo->addFile($filename);
    $repo->commit('init commit');
  }

  return [$directory, $repo ?? null];
}
