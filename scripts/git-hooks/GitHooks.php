<?php

/**
 * @file
 * Contains \jover\drupal_pre_commit\GitHooks.
 */

namespace jover\drupal_pre_commit;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class GitHooks {

  protected static function getProjectRoot() {
    return getcwd();
  }

  protected static function getGitHooksOriginDirectory() {
    $project_root = static::getProjectRoot();
    return $project_root . '/scripts/git-hooks';
  }

  protected static function getGitHooksTargetDirectory() {
    $project_root = static::getProjectRoot();
    return $project_root . '/.git/hooks';
  }

  public static function create(Event $event) {
    $fs = new Filesystem();
    $origin_dir = static::getGitHooksOriginDirectory();
    $target_dir = static::getGitHooksTargetDirectory();

    $git_hooks = [
      'pre-commit',
    ];

    foreach ($git_hooks as $git_hook) {
      $origin_file = $origin_dir . '/' . $git_hook;
      $target_file = $target_dir . '/' . $git_hook;

      if ($fs->exists($origin_file)) {
        // Symlink the target to origin (force copy on Windows).
        $fs->symlink($origin_file, $target_file, TRUE);
        // Make the file executable.
        $fs->chmod($target_file, 0775);

        $event->getIO()->write("Create a symbolic link for Git Hook: " . $git_hook);
      }
    }
  }

}
