<?php
/**
 * DrupalCodeCheck_Sniffs_JS_ForbiddenObjectFunctionCallsSniff.
 *
 * Discourages the use blacklisted JavaScript functions.
 * Can be used to forbid the use of any function.
 *
 * @see Generic_Sniffs_PHP_ForbiddenFunctionsSniff
 */
class DrupalCodeCheck_Sniffs_JS_ForbiddenObjectFunctionCallsSniff extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff {

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
    // JavaScript object function calls.
    'console.error' => null,
    'console.info'  => null,
    'console.log'   => null,
    'window.alert'  => null,
  );

  /**
   * {@inheritdoc}
   */
  public $error = true;

  /**
   * {@inheritdoc}
   */
  public function register()
  {
    return array_unique(array_merge(
      parent::register(),
      array(T_STRING, T_OBJECT_OPERATOR, T_FUNCTION)
    ));
  }//end register()

  /**
   * {@inheritdoc}
   */
  public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
  {
    $tokens = $phpcsFile->getTokens();
    $prevToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
    $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
    $afterNextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 2), null, true);

    // Check for forbidden JavaScript object function calls (e.g.: console.log).
    if (
      $tokens[$stackPtr]['code'] === T_OBJECT_OPERATOR &&      // console
      $tokens[$prevToken]['code'] === T_STRING &&              // .
      $tokens[$nextToken]['code'] === T_STRING &&              // log
      $tokens[$afterNextToken]['code'] === T_OPEN_PARENTHESIS  // (
    ) {
      $function = strtolower(
        $tokens[$prevToken]['content'] .
        $tokens[$stackPtr]['content'] .
        $tokens[$nextToken]['content']
      );

      $pattern  = null;

      if ($this->patternMatch === true) {
        $count   = 0;
        $pattern = preg_replace(
          $this->forbiddenFunctionNames,
          $this->forbiddenFunctionNames,
          $function,
          1,
          $count
        );

        if ($count === 0) {
          return;
        }

        // Remove the pattern delimiters and modifier.
        $pattern = substr($pattern, 1, -2);
      } else {
        if (in_array($function, $this->forbiddenFunctionNames) === false) {
          return;
        }
      }//end if

      $this->addError($phpcsFile, $prevToken, $function, $pattern);
    }

    return;
  }//end process()

}//end class
