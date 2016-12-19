<?php

/**
 * @file
 * Drupal Code Check Application, to be used as a Git pre-commit hook.
 */

require __DIR__ . '/../../../../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Drupal Code Check abstract base class.
 */
abstract class DrupalCodeCheck extends Application {

  protected $input;
  protected $output;

  protected $succeed;

  /**
   * {@inheritdoc}
   */
  public function doRun(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;

    $this->setSucceed(TRUE);

    $this->setAutoExit(FALSE);

    $this->bootstrapMessage();

    $this->processFiles();
  }

  /**
   * Set the succeed value.
   *
   * @param bool $succeed
   *   True or false whether this app's result is succeeded or not.
   */
  protected function setSucceed($succeed) {
    $this->succeed = $succeed;
  }

  /**
   * Get the succeed value.
   *
   * @return bool
   *   Returns true or false whether this app's result is succeeded or not.
   */
  public function getSucceed() {
    return $this->succeed;
  }

  /**
   * Render a message to be displayed on bootstrapping the application.
   */
  abstract protected function bootstrapMessage();

  /**
   * Fetching the committed files.
   *
   * @return array
   *   An array with all the committed files.
   */
  protected function fetchCommittedFiles() {
    $output = array();
    $return = 0;

    exec('git rev-parse --verify HEAD 2> /dev/null', $output, $return);

    $against = ($return == 0) ? 'HEAD' : '4b825dc642cb6eb9a060e54bf8d69288fbee4904';

    exec("git diff --cached --name-only {$against} --diff-filter=ACM", $output);

    return $output;
  }

  /**
   * Process the files.
   */
  protected function processFiles() {
    $files = $this->fetchCommittedFiles();
    $files = $this->filterFiles($files);

    foreach ($files as $file) {
      $this->output->writeln('<info>Process file:</info> ' . $file);

      if (!$this->processFile($file)) {
        $this->setSucceed(FALSE);
      }

      $this->output->writeln('');
    }
  }

  /**
   * Filter out files which do not may be checked.
   */
  protected function filterFiles($files) {
    $allowed_extensions = [
    // Include files.
      'inc',
    // Install files.
      'install',
    // JavaScript files.
      'js',
    // MarkDown files.
      'md',
    // Module files.
      'module',
    // Regular PHP files.
      'php',
    // Profile files.
      'profile',
    // Test files.
      'test',
    // Theme files.
      'theme',
    // Text files.
      'txt',
    // YAML files.
      'yml',
    ];
    $extension_pattern = '/(\.' . implode(')|(\.', $allowed_extensions) . ')$/';

    $excluded_paths = [
    // Drupal core files.
      'web\/core\/',
    // Contributed library files.
      'web\/libraries\/contrib\/',
    // Contributed module files.
      'web\/modules\/contrib\/',
    // Contributed profile files.
      'web\/profiles\/contrib\/',
    // Contributed theme files.
      'web\/themes\/contrib\/',
    // Contributed drush files.
      'drush\/contrib\/',
    ];
    $excluded_path_pattern = '/(^' . implode('.+)|(^', $excluded_paths) . '.+)/';

    foreach ($files as $index => $file) {
      // Keep files with allowed extensions.
      if (!preg_match($extension_pattern, $file)) {
        unset($files[$index]);
      }

      // Ignore files located in excluded paths.
      if (preg_match($excluded_path_pattern, $file)) {
        unset($files[$index]);
      }
    }

    return $files;
  }

  /**
   * Processing a single file.
   *
   * @param string $file
   *   Path to the file to be processed.
   *
   * @return bool
   *   Returns true or false whether the processing was successful or not.
   */
  abstract protected function processFile($file);

}

/**
 * Drupal PHP Code check class.
 */
class DrupalPhpCodeCheck extends DrupalCodeCheck {

  // PHP Code Sniffer.
  const PHPCS_BIN = 'vendor/bin/phpcs';
  // PHP Code Beautifier.
  const PHPCBF_BIN = 'vendor/bin/phpcbf';

  // PHP syntax-error free code.
  const NO_SYNTAX_ERROR = 0;
  // PHP syntax error code.
  const SYNTAX_ERROR = 255;
  // Code Sniffer success code.
  const PHPCS_PASSED = 0;
  // Code Sniffer error code.
  const PHPCS_FAILED = 1;
  // PHPCBF success code.
  const PHPCBF_PASSED = 0;
  // PHPCBF error code.
  const PHPCBF_FAILED = 1;

  /**
   * {@inheritdoc}
   */
  protected function bootstrapMessage() {
    $this->output->writeln('*******************************');
    $this->output->writeln('*   DRUPAL CODE - PHP CHECK   *');
    $this->output->writeln('*******************************');
    $this->output->writeln('');
  }

  /**
   * {@inheritdoc}
   */
  protected function processFile($file) {
    $succeed = TRUE;

    $processBuilder = new ProcessBuilder(['php', '-l', $file, '>&2']);
    $process = $processBuilder->getProcess();
    $status = $process->run();

    if ($status == self::NO_SYNTAX_ERROR) {
      if ($this->codeFixer($file)) {
        if (!$this->codeSniffer($file)) {
          $succeed = FALSE;
        }
      }
      else {
        $succeed = FALSE;
      }
    }
    else {
      if ($status == self::SYNTAX_ERROR) {
        $this->output->writeln('You have syntax error in your code. Please fix and commit your changes again.');
      }
      else {
        $this->output->writeln('Invalid operation.');
      }

      $succeed = FALSE;
    }

    return $succeed;
  }

  /**
   * Fixing the code of a file.
   *
   * @param string $file
   *   Path to the file to be checked for fixing.
   *
   * @return bool
   *   Returns true or false whether the processing was successful or not.
   */
  protected function codeFixer($file) {
    $succeed = TRUE;

    $processBuilder = new ProcessBuilder([
      self::PHPCS_BIN,
      '--config-set',
      'installed_paths',
      'vendor/drupal/coder/coder_sniffer',
    ]);
    $process = $processBuilder->getProcess();
    $process->run();

    $processBuilder = new ProcessBuilder([
      self::PHPCBF_BIN,
      '--standard=./vendor/jover_be/drupal-code-check/DrupalCodeCheck',
      '-p',
      $file,
    ]);
    $process = $processBuilder->getProcess();
    $status = $process->run();

    if ($status == self::PHPCBF_PASSED) {
      $this->output->writeln('No formatting errors to be automatically fixed.');
    }
    else {
      if ($status == self::PHPCBF_FAILED) {
        $this->output->writeln('PHP Code Sniffer Beautifier and Fixer automatically fixed some coding style issues. Please stage these changes and commit again.');
      }
      else {
        $this->output->writeln('Invalid operation.');
      }

      $succeed = FALSE;
    }

    if (!$process->isSuccessful()) {
      $this->output->writeln(sprintf('<error>%s</error>', trim($process->getOutput())));
      $succeed = FALSE;
    }

    return $succeed;
  }

  /**
   * Sniffing the code of a file.
   *
   * @param string $file
   *   Path to the file to be checked for sniffing.
   *
   * @return bool
   *   Returns true or false whether the processing was successful or not.
   */
  protected function codeSniffer($file) {
    $succeed = TRUE;

    $processBuilder = new ProcessBuilder([
      self::PHPCS_BIN,
      '--standard=./vendor/jover_be/drupal-code-check/DrupalCodeCheck',
      '-p',
      $file,
    ]);
    $process = $processBuilder->getProcess();
    $status = $process->run();

    if ($status == self::PHPCS_PASSED) {
      $this->output->writeln('No formatting errors detected.');
    }
    else {
      if ($status == self::PHPCS_FAILED) {
        $this->output->writeln('You have coding standard problem in your code. Please fix and commit your changes again.');
      }
      else {
        $this->output->writeln('Invalid operation.');
      }

      $succeed = FALSE;
    }

    if (!$process->isSuccessful()) {
      $this->output->writeln(sprintf('<error>%s</error>', trim($process->getOutput())));
      $succeed = FALSE;
    }

    return $succeed;
  }

}

/**
 * Drupal Blacklisted Strings Check class.
 */
class DrupalBlacklistedStringsCheck extends DrupalCodeCheck {

  /**
   * {@inheritdoc}
   */
  protected function bootstrapMessage() {
    $this->output->writeln('***********************************************');
    $this->output->writeln('*   DRUPAL CODE - BLACKLISTED STRINGS CHECK   *');
    $this->output->writeln('***********************************************');
    $this->output->writeln('');
  }

  /**
   * Returns an array with the checks / blacklisted strings.
   *
   * @return array
   *   An array with all the blacklisted strings.
   */
  protected static function getChecks() {
    return [
      // Code conflicts resulting from Git merge.
      '<<<<<<<',
      '>>>>>>>',
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function processFile($file) {
    $succeed = TRUE;

    $file_contents = file_get_contents($file);

    $checks = $this->getChecks();
    foreach ($checks as $index => $check) {
      $checks[$index] = preg_quote($check);
    }

    // \b = Any word boundary.
    $pattern = '/\b(' . implode(')|\b(', $checks) . ')/';

    if ($matches = preg_match_all($pattern, $file_contents, $output_array)) {
      $checks_found = [];

      foreach ($output_array as $output) {
        foreach ($output as $check) {
          $check = trim($check);

          if (!empty($check)) {
            $checks_found[$check] = $check;
          }
        }
      }

      foreach ($checks_found as $check) {
        // If the check ends with "(", add a ")" for a nicer message.
        if (preg_match('/\($/', $check)) {
          $check = $check . ')';
        }

        $this->output->writeln(sprintf('<error>%s</error>', "{$check} found in file: {$file}"));
      }

      $succeed = FALSE;
    }

    if ($succeed) {
      $this->output->writeln('No blacklisted strings found.');
    }

    return $succeed;
  }

}

/**
 * Drupal Code Check Application class.
 */
class DrupalCodeCheckApplication extends Application {

  private $input;
  private $output;

  private $succeed;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct('Drupal Code Check', '0.0.9');
  }

  /**
   * {@inheritdoc}
   */
  public function doRun(InputInterface $input, OutputInterface $output) {
    $this->input = $input;
    $this->output = $output;

    $this->succeed = TRUE;

    $this->bootstrapMessage();

    $drupal_php_code_check = new DrupalPhpCodeCheck();
    $drupal_php_code_check->run($this->input, $this->output);
    if (!$drupal_php_code_check->getSucceed()) {
      $this->succeed = FALSE;
    }

    $drupal_blacklisted_strings_check = new DrupalBlacklistedStringsCheck();
    $drupal_blacklisted_strings_check->run($this->input, $this->output);
    if (!$drupal_blacklisted_strings_check->getSucceed()) {
      $this->succeed = FALSE;
    }

    if (!$this->succeed) {
      throw new Exception('There are errors! Please fix them and try again...');
    }
  }

  /**
   * Render a message to be displayed on bootstrapping the application.
   */
  protected function bootstrapMessage() {
    $this->output->writeln('***********************************************************************');
    $this->output->writeln('*                                                                     *');
    $this->output->writeln('*   GIT PRE-COMMIT HOOK FOR DRUPAL                                    *');
    $this->output->writeln('*                                                                     *');
    $this->output->writeln('*   In order to commit your changes, it must pass the four filters:   *');
    $this->output->writeln('*   I.   Syntax checking using PHP Linter                             *');
    $this->output->writeln('*   IIa. Automatically try to match code style via PHPCBF             *');
    $this->output->writeln('*   IIb. Coding standards checking using PHP Code Sniffer             *');
    $this->output->writeln('*   III. Blacklisted strings checking/validation.                     *');
    $this->output->writeln('*                                                                     *');
    $this->output->writeln('***********************************************************************');
    $this->output->writeln('');
  }

}
