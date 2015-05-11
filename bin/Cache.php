<?php
 /**
 * File / Class short descr
 *
 * [Files / Class long descr]
 *
 * @package    apien
 * @author     Nathanael Tombs ngctombs@gmail.com
 * @author     Eleonore Fournier-Tombs eleonorefournier@gmail.com
 * @license    GNU General Public License, version 3 (GPL-3.0)
 * @link       http://opensource.org/licenses/GPL-3.0
 * @date       2/11/2015
 * @time       10:51 PM
 */

class Cache
{
    private $_database;
    private $_key;
    private function __construct()
    {
        $this->_database = Database::getInstance();
    }

    /**
     * Returns the static singleton instance of this Database.
     *
     * @return Cache The singleton instance.
     */
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance)
            $instance = new static();
        return $instance;
    }

    private function rsort($arr)
    {
        if (array_keys($arr) !== range(0, count($arr) - 1))
            ksort($arr);
        else
            sort($arr);
        foreach ($arr as $key=>$val)
            if (is_array($val))
                $arr[$key] = $this->rsort($val);
        return $arr;
    }

    public function generateKey($resources, $options)
    {
        if (isset($this->_key)) return $this->_key;
        return $this->_key = gzcompress(json_encode($this->rsort($resources) + $this->rsort($options)));
    }

    public function getValue($resources, $options)
    {
        $query = "SELECT `value` FROM `cache` WHERE `key` LIKE ?";

        $this->generateKey($resources, $options);

        $db =& $this->_database->_connection;

        $stmt = $db->prepare($query);
        $stmt->bind_param('s', $this->_key);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['value'];
    }

    public function setValue($response, $resources = array(), $options = array())
    {
        $this->generateKey($resources, $options);
        try {
            $query = "INSERT INTO `cache` (`key`, `value`) VALUES (?, ?)";
            $db =& $this->_database->_connection;

            $stmt = $db->prepare($query);
            $stmt->bind_param('ss', $this->_key, $response);
            $stmt->execute();
            $stmt->get_result();
        } catch (Exception $e) {
            restLog(print_r($e->getMessage(), true), 'cache_error.log');
        }
    }
}
