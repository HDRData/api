<?php
/**
 * The MySQL Database object
 *
 * @package    apien
 * @author     Nathanael Tombs ngctombs@gmail.com
 * @author     Eleonore Fournier-Tombs eleonorefournier@gmail.com
 * @license    GNU General Public License, version 3 (GPL-3.0)
 * @link       http://opensource.org/licenses/GPL-3.0
 * @date       2/15/2015
 * @time       10:14 PM
 */

class Database
{
    /**
     * An array of configuration keys for the config to be validated against
     * @var array
     */
    private $_keys = array('host', 'user', 'pass', 'name');
    /**
     * The MySQL database connection
     * @var mysqli
     */
    public $_connection;
    /**
     * The XML config object
     * @var SimpleXMLElement
     */
    private $_config;
    /**
     * The database version
     * @var float
     */
    private $_version = 0;
    /**
     * The name of the install script
     * @var string
     */
    private $_install = 'install-0.01.sql.gz';
    /**
     * Connection timeout
     * @var int
     */
    private $_timeout = 30;

    /**
     * Initiates the database connection
     *
     * Gets the database connection values from the configuration file
     * Validates the connection values
     * Establishes the MySQLi connection
     * Runs install / update scripts if necessary
     *
     * @throws Exception Configuration file not found
     * @throws Exception Configuration key not found
     * @throws Exception Database connection failed
     * @throws Exception Install script not found
     */
    private function __construct()
    {
        //Checks if the configuration file exists
        if (!is_file(CONFIG_FILE))
            throw new Exception('The configuration file was not found');

        //Gets the configuration file contents
        $this->_config = simplexml_load_file(CONFIG_FILE)->db;

        //Validates the configuration file keys
        foreach ($this->_keys as $key)
            if (!isset($this->_config->{$key}))
                throw new Exception ("The key $key is not present in the configuration file");

        $this->_connection = mysqli_init();

        $this->_connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, $this->_timeout);

        //Establishes the MySQLi database connection
        $this->_connection->real_connect(
            $this->_config->{'host'}, $this->_config->{'user'}, $this->_config->{'pass'}, $this->_config->{'name'}
        );

        //Validates the database connection
        if ($this->_connection->connect_errno)
            throw new Exception('Database connection failed with: '  . $this->_connection->connect_error);

        //Gets a list of tables from the database
        $tables = array_map(function($key) {return $key[0];}, $this->query('SHOW TABLES')->fetch_all());

        //Gets a list of files from the SQL directories, ignoring current and back
        $files = array_diff(scandir(SQL_DIRECTORY), array('.', '..'));

        //If the version table isn't present, runs the install script
        if (!in_array('version', $tables)) {
            //If the install script isn't found, throws an exception
            if (!in_array($this->_install, $files))
                throw new Exception("Initial install script {$this->_install} not found");

            //Runs the install script
            $this->run($this->_install);
        }

        //Gets the current database version
        $this->getVersion();
        //Runs each update script if their version exceeds the current database version
        foreach($files as $file)
            $this->run($file);
    }

    /**
     * Returns the static singleton instance of this Database.
     *
     * @return Database The singleton instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance)
            $instance = new static();
        return $instance;
    }

    /**
     * Fetches the database version from the version table and stores it in the _version variable
     *
     * @return float Database version
     */
    private function getVersion()
    {
        $row = $this->query("SELECT `value` FROM `version` WHERE `key` = 'database'")->fetch_assoc();
        $this->_version = (float)$row['value'];
        return $this->_version;
    }

    /**
     * Sets the MySQL database version and stores it in the _version variable
     *
     * @param float $version Database version
     * @throws Exception Invalid version provided
     */
    private function setVersion($version)
    {
        if ((float)$version != $version)
            throw new Exception("The database version: $version is not a valid float");
        $this->_version = $version;

        $this->query("INSERT INTO `version` VALUES ('database', $version) ON DUPLICATE KEY UPDATE `value`=$version");
    }

    /**
     * Runs an SQL update or install script and updates the database version
     *
     * @param string $file the name of the install or update script
     */
    private function run($file)
    {
        $path = SQL_DIRECTORY.$file;
        if (!preg_match('~^(update|install)-([0-9]+\.[0-9]+)~i', $file, $match) || $this->_version >= $match[2]) return;

        //Checks if the file is GZipped
        if (preg_match('~\.gz$~i', $file))
            $query = implode("\n", gzfile($path));
        else
            $query = file_get_contents($path);

        //Runs a multi_query on the query string
        $this->query($query, true);

        //Sets the updated database version
        $this->setVersion($match[2]);
    }

    /**
     * Runs one simple or composite query string and returns the query's result
     *
     * @param string $query
     * @param bool $multi_query Is a composite query string
     * @return mysqli_result
     * @throws Exception SQL query failed
     */
    public function query($query, $multi_query = false)
    {
        if ($multi_query)
            $result = $this->_connection->multi_query($query);
        else
            $result = $this->_connection->query($query);

        //Checks for a failed query
        if(!$result)
            throw new Exception("Query $query failed with error {$this->_connection->error}");

        //Flush the query results, allows for multiple consecutive queries on one connection
        if ($multi_query)
            while ($this->_connection->more_results()) {$this->_connection->next_result();}

        return $result;
    }

    public function prepare($query, $types, $values)
    {
        $stmt = $this->_connection->prepare($query);
        $stmt->bind_param($types, $values);
        $stmt->execute();
        $stmt->bind_result($district);

        /* fetch value */
        $stmt->fetch();

        /* close statement */
        $stmt->close();
    }
}
