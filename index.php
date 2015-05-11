<?php
/**
 * Index file
 *
 * Sets the constants
 * Enforces file structure integrity
 * Set Error & Exception handlers which log the errors
 * Defines the autoloader
 * Instantiates the API
 *
 * @package    apien
 * @author     Nathanael Tombs ngctombs@gmail.com
 * @author     Eleonore Fournier-Tombs eleonorefournier@gmail.com
 * @license    GNU General Public License, version 3 (GPL-3.0)
 * @link       http://opensource.org/licenses/GPL-3.0
 * @date       2/11/2015
 * @time       10:13 PM
 */

define('LOGGING', true);
define('DATE_FORMAT', 'm.d.y H:m:s');

/**
 * Predefined directories
 */
define('ROOT_DIRECTORY', dirname(__FILE__).'/');
define('BIN_DIRECTORY', ROOT_DIRECTORY.'bin/');
define('SQL_DIRECTORY', ROOT_DIRECTORY.'sql/');
define('ETC_DIRECTORY', ROOT_DIRECTORY.'etc/');
define('VAR_DIRECTORY', ROOT_DIRECTORY.'var/');
define('LOG_DIRECTORY',  VAR_DIRECTORY.'log/');

define('CONFIG_FILE', ETC_DIRECTORY.'config.xml');

/**
 * Populates the directory tree to ensure file structure integrity
 */
$dirs = array(BIN_DIRECTORY, SQL_DIRECTORY, ETC_DIRECTORY, VAR_DIRECTORY, LOG_DIRECTORY);
foreach ($dirs as $dir)
    if (!is_dir($dir))
        mkdir($dir);

/**
 * Standard logging function appends a print_r of the provided data to the specified filename
 *
 * @param mixed $data The data to be logged
 * @param string $file The file to be logged to
 */
function restLog($data, $file = 'system.log')
{
    if (!LOGGING) return;
    file_put_contents(LOG_DIRECTORY.$file, print_r(array(date(DATE_FORMAT), $data), true), FILE_APPEND);
}


/**
 * Custom exception handler logs the exception and outputs the message to console
 * @param Exception $e
 * @throws Exception $e
 */
function exceptionHandler($e)
{
    restLog(
        "{$e->getFile()}:{$e->getLine()}\n".
        "{$e->getMessage()}\n".
        "{$e->getTraceAsString()}\n",
        'exception.log'
    );
    Api::error(500, $e->getMessage());
}

/**
 * Custom error handler logs the error and outputs the message to console
 *
 * @param int $no Error level
 * @param string $str Error message
 * @param string $file File in which the error occurred
 * @param int $line Line at which the error occurred
 */
function errorHandler($no, $str, $file, $line)
{
    restLog(
        "Warning l.$no occured in $file at line $line\n".
        "$str\n",
        'error.log'
    );
}

/**
 * The autoloader replaces underscores with slashes, appends .php to the class name
 * and looks for it in the bin directory.
 *
 * e.g. Database would be found in bin/Database.php
 * @param string $class the class name
 * @throws Exception File not found
 * @throws Exception Class mismatch
 */
function __autoload($class)
{
    $path = 'bin/'.preg_replace('~_~', '/', $class).'.php';
    if (!is_file($path))
        throw new Exception("Class $class's inclusion file $path was not found.");
    include_once($path);
    if (!class_exists($class))
        throw new Exception("Class $class not found in file $path");
}

/**
 * Set the custom exception handler, default timezone and instantiate the base class
 */
date_default_timezone_set('America/Montreal');
set_exception_handler('exceptionHandler');
set_error_handler('errorHandler');

/**
 * Log the request data
 */
if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else $ip = $_SERVER["REMOTE_ADDR"];
if (preg_match('~^[0-9:\.]*$~', $ip))
    Database::getInstance()->query("INSERT INTO `request_log` (`ip`, `request`) VALUES ('$ip', '{$_GET['request']}')");

/**
 * Instantiate the API
 */
Api::getInstance();