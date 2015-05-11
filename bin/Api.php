<?php
/**
 * Rest API base class
 *
 * @package    apien
 * @author     Nathanael Tombs ngctombs@gmail.com
 * @author     Eleonore Fournier-Tombs eleonorefournier@gmail.com
 * @license    GNU General Public License, version 3 (GPL-3.0)
 * @link       http://opensource.org/licenses/GPL-3.0
 * @date       2/11/2015
 * @time       10:26 PM
 */

class Api
{
    /**
     * Stores the database instance
     * @var Database
     */
    private $_connection;
    /**
     * Validate the resource inputs w/ RegEx (must match the DB columns / values)
     * @var array
     */
    private $_resource_validation = array(
        'country_code' => '~^[a-z]{3}$~i',
        'indicator_id' => '~^[0-9]+$~',
        'year'         => '~^[0-9]{4}$~'
    );
    /**
     * Validate the GET option inputs w/ RegEx
     * @var array
     */
    private $_option_validation = array(
        'gzip' => '~^(true|false)$~',
        'language' => '~^[a-z]{2}$~i',
        'structure' => '~^(ciy|cyi|yci|yic|icy|iyc|false)$~i',
        'pretty' => '~^(true|false)$~'
    );

    /**
     * Used to store the Query's primary resource values (year, country, indicator)
     * @var array
     */
    private $_resources = array();

    /**
     * Default option values
     * @var array
     */
    private $_options = array(
        'language' => 'EN',
        'structure' => 'false',
        'gzip' => 'false',
        'pretty' => 'false'
    );

    /**
     * Fetches the Database object
     * Parses & validates the resources & options
     * Generates & runs the query
     */
    private function __construct()
    {
        $this->_connection = Database::getInstance();

        $this->parseResources(array_diff(explode('/', $_GET['request']), array('')));
        $this->parseOptions();


        $this->_cache = Cache::getInstance();
        $value = $this->_cache->getValue($this->_resources, $this->_options);
        if (!$value) {
            $value = $this->fetchData();
            $this->_cache->setValue($value);
        }

        if ($this->_options['pretty'] != 'false')
            self::prettyPrint($value);
        else
            self::output($value);
    }

    /**
     * Returns the static singleton instance of this Database.
     *
     * @return Api The singleton instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance)
            $instance = new static();
        return $instance;
    }

    /**
     * Parses & validates the resources passed in the URL
     *
     * @param array $requests
     * @throws Exception resource validation failed
     */
    public function parseResources($requests)
    {
        foreach($requests as $key => $request)
        {
            if (!in_array($request, array_keys($this->_resource_validation))) continue;

            $values = $requests[$key+1];
            if (!isset($values))
                self::error(400, "Resource value not specified for resource $request");

            //String newline, space or tab characters and any trailing commas and explode CSV
            $values = explode(',', preg_replace('~(\t\s\n|,+$|^,+)~', '', preg_replace('~,+~', ',', $values)));

            foreach ($values as $value)
                if (!preg_match($this->_resource_validation[$request], $value))
                    self::error(400, "Invalid value $value provided for resource $request.");

            $this->_resources[$request] = $values;
        }

        /*
        Removed requirement to have at least one resource specified
        if (empty($this->_resources))
            self::error(400, "No primary resources have been specified");
        */
    }

    /**
     *  Parses & validates the options passed as GET parameters & sets them in $this->_options
     *
     * @throws Exception option validation failed
     */
    private function parseOptions()
    {

        foreach ($this->_option_validation as $option => $validation)
        {
            $value =& $_GET[$option];
            if (!isset($value)) continue;
            if (!preg_match($validation, $value))
                self::error(400, "Invalid value $value provided for option $option.");

            $this->_options[$option] = $value;
        }
    }

    /**
     * Generates & runs the query
     *
     * @return string [Gzipped] JSON string
     * @throws Exception Empty response
     */
    private function fetchData()
    {
        $query =
            "SELECT `iv`.`country_code`, `iv`.`indicator_id`, `iv`.`year`, `iv`.`value`,
                `cn`.`name` AS 'country_name', `in`.`name` AS 'indicator_name'
             FROM `indicator_value` AS `iv`
             INNER JOIN `country_name` as `cn` ON `cn`.`code` = `iv`.`country_code`
             INNER JOIN `indicator_name` as `in` ON `in`.`id` = `iv`.`indicator_id`
             WHERE `cn`.`language` = '{$this->_options['language']}'
             AND   `in`.`language` = '{$this->_options['language']}'";

        foreach ($this->_resources as $resource => $values)
            $query .= " AND `iv`.`$resource` IN (".preg_replace('~([a-z0-9]+)(,?)~i', '"$1"$2', implode(',',$values)).")";

        //Check for forbidden SQL keywords
        if (preg_match('~(UPDATE|INSERT|DROP|DELETE|TRUNCATE|DATABASE|TABLE|ALTER|ROLLBACK|CREATE|KILL)~i', $query, $match))
            self::error(403, "The query includes the forbidden keyword {$match[1]}");

        $result = $this->_connection->query($query);

        $response = array();
        while ($row = $result->fetch_assoc())
            $response[] = $row;


        if ($this->_options['structure'] != 'false')
            $response = $this->structureData($response);
        else
            $response = $this->cleanData($response);

        if (empty($response))
            self::error(404, "No data was found for the request {$_GET['request']}");

        $response = self::prepareResponse(200, $response);
        if ($this->_options['gzip'] != 'false')
            return gzcompress($response);

        return $response;
    }

    /**
     * Structures data output as a multidimensional array using the hierarchy specified with the structure option
     *
     * @param $results
     * @return array
     */
    private function structureData($results)
    {
        $structured_results = array();
        $real_val = array('c' => 'country_code', 'i' => 'indicator_id', 'y' => 'year');
        $keys = str_split($this->_options['structure']);

        foreach ($keys as $key => $val)
            $keys[$key] = $real_val[$val];

        foreach ($results as $result) {
            $structured_results['indicator_value'][$result[$keys[0]]][$result[$keys[1]]][$result[$keys[2]]] = $result['value'];
            $structured_results['country_name'][$result['country_code']] = $result['country_name'];
            $structured_results['indicator_name'][$result['indicator_id']] = $result['indicator_name'];
        }

        return $structured_results;
    }

    private function cleanData($results)
    {
        $clean_results = array();

        foreach ($results as $result) {
            $clean_results['indicator_value'][]= array($result['country_code'], $result['indicator_id'], $result['year'], $result['value']);
            $clean_results['country_name'][$result['country_code']] = $result['country_name'];
            $clean_results['indicator_name'][$result['indicator_id']] = $result['indicator_name'];
        }

        return $clean_results;
    }

    public static function error($code, $body)
    {
        self::output(self::prepareResponse($code, $body));
    }

    private static function output($str)
    {
        header('Content-Type: application/json');
        echo $str;
        die();
    }

    private static function prettyPrint($str)
    {
        $json = json_decode($str);
        echo '<pre>';
        echo json_encode($json, JSON_PRETTY_PRINT);
        echo '</pre>';
        die();
    }

    private static function prepareResponse($code, $body)
    {
        http_response_code($code);
        return json_encode($body);
    }
}