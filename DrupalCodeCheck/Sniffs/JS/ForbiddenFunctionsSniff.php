<?php
/**
 * DrupalCodeCheck_Sniffs_JS_ForbiddenFunctionsSniff.
 *
 * Discourages the use blacklisted JavaScript functions.
 * Can be used to forbid the use of any function.
 *
 * @see Generic_Sniffs_PHP_ForbiddenFunctionsSniff
 */
class DrupalCodeCheck_Sniffs_JS_ForbiddenFunctionsSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff {

  /**
   * {@inheritdoc}
   */
  public $supportedTokenizers = array(
    'JS',
  );

  /**
   * {@inheritdoc}
   */
  public $forbiddenFunctions = array(
    // JavaScript debugging functions.
    'alert'       => null,
    'console\\.log' => null,
  );

  /**
   * {@inheritdoc}
   */
  protected $patternMatch = false;

  /**
   * {@inheritdoc}
   */
  public $error = true;

}