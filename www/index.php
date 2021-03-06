<?php
/**
 * PHP/Apache/Markdown DocBook
 * @package 	DocBook
 * @license   	GPL-v3
 * @link      	https://github.com/atelierspierrot/docbook
 */

// Show errors at least initially
@ini_set('display_errors','1'); @error_reporting(E_ALL ^ E_NOTICE);

// Set a default timezone to avoid PHP5 warnings
$default_timezone = @date_default_timezone_get();
@date_default_timezone_set( isset($default_timezone) ? $default_timezone : 'Europe/London' );

// -----------------------------------
// Get Composer autoloader
// -----------------------------------

$composerAutoLoader = __DIR__.'/../src/vendor/autoload.php';
if (@file_exists($composerAutoLoader)) {
    require_once $composerAutoLoader;
} else {
    die("You need to run Composer on the project to build dependencies and auto-loading"
    ." (see: <a href=\"http://getcomposer.org/doc/00-intro.md#using-composer\">http://getcomposer.org/doc/00-intro.md#using-composer</a>)!");
}

// -----------------------------------
// PROCESS
// -----------------------------------

// the application 
\DocBook\FrontController::getInstance()->distribute();

// Endfile
