<?php
/**
 * DrupalCodeCheck_Sniffs_PHP_ForbiddenFunctionsSniff.
 *
 * Discourages the use blacklisted PHP functions.
 * Can be used to forbid the use of any function.
 *
 * @see Generic_Sniffs_PHP_ForbiddenFunctionsSniff
 */
class DrupalCodeCheck_Sniffs_PHP_ForbiddenFunctionsSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff {

  /**
   * {@inheritdoc}
   */
  public $forbiddenFunctions = array(
    // PHP Blacklisted functions.
    'die',
    'print_r',
    'var_dump',
    // Drupal's build-in debugging functions.
    'debug',
    // Devel's module debugging functions.
    'dd',
    'ddebug_backtrace',
    'dpm',
    'dpq',
    'dpr',
    'dprint_r',
    'drupal_debug',
    'dsm',
    'dvm',
    'dvr',
    'kdevel_print_object',
    'kpr',
    'kprint_r',
    'krumo',
  );

}//end class