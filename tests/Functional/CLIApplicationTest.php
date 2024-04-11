<?php

use App\CLIApplication;
use Laravel\Prompts\Key;
use \Laravel\Prompts\Prompt;

test('cli not initialized', function () {
  Prompt::fake();

  $cli = new CLIApplication([]);
  $cli->run();

  Prompt::assertOutputContains('No lazywt.json file found. Please run `lazywt init` in the root of your project.');
});

test('create with invalid repo', function () {
  $repo = str_split("invalid");

  Prompt::fake([
    ...$repo, Key::ENTER
  ]);

  $cli = new CLIApplication([
    'init'
  ]);

  $cli->run();

  Prompt::assertOutputContains('The repository does not exist');
});
