<?php

/**
 * /lib/db.php
 */

/**
 * Database instance object
 *
 * @author Carl Markham <carl@cjmarkham.co.uk>
 * @version 1.0
 * @package Squirrel
 * @uses PDO
 */
class DbInstance extends PDO
{

	/**
	 * Whether we are in Debug mode
	 *
	 * @access public
	 * @var bool
	 */
	public $debug = true;

	/**
	 * The PDO connection
	 *
	 * @access public
	 * @var object
	 *
	 */
	public $connection;

	/**
	 * The number of queries excecuted
	 *
	 * @access public
	 * @var int
	 *
	 */
	public $queries = 0;

	/**
	 * A list of ran queries
	 *
	 * @access public
	 * @var array
	 *
	 */
	public $queryList = array();

	/**
	 * Connects to the database using PDO
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @param array $config The config variables
	 *
	 * @return boolean
	 *
	 */
	public function __construct(array $config)
	{
		if (empty($this->connection))
		{
			try
			{
				$this->connection = parent::__construct('mysql:host=' . $config['host'] . ';dbname=' . $config['name'], $config['user'], $config['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}
			catch (PDOException $e)
			{
				die('Sorry that page could not be displayed');
			}
		}

		return true;
	}

	/**
	 * Escape a string or array
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @param string $elem The string to escape
	 *
	 * @return string The escaped string
	 *
	 */
	public function escape($elem)
	{

		if (!is_array($elem))
		{
			$elem = htmlentities($elem);
		}
		else
		{
			foreach ($elem as $key => $value)
			{
				$elem[$key] = self::escape($value);
			}
		}

		return $elem;
	}

	/**
	 * Fetch one result from the database
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @param string $sql The SQL query to excecute
	 * @param array $replacements The items to replace the placeholders in the SQL
	 *
	 * @return array The data in the first result row
	 *
	 */
	public function fetch_one($sql, array $replacements = array())
	{
		$sth = self::prex($sql, $replacements);
		$result = $sth->fetch();

		if (!is_array($result))
		{
			return $result;
		}

		return reset($result);
	}

	/**
	 * Fetch all results from the database
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @param string $sql The SQL query to excecute
	 * @param array $replacements The items to replace the placeholders in the SQL
	 *
	 * @return array The data returned from the query
	 *
	 */
	public function fetch_results($sql, array $replacements = array())
	{
		$data = array();
		$sth = self::prex($sql, $replacements);
		while ($results = $sth->fetch())
		{
			$data[] = $results;
		}

		return $data;
	}

	/**
	 * Fetch all results from the database and define the key as the array key
	 *
	 * @author David Balmbra <dave@fog.com>
	 * @param string $sql The SQL query to excecute
	 * @param array $replacements The items to replace the placeholders in the SQL
	 *
	 * @return array The data returned from the query
	 *
	 */
	public function fetch_results_with_key($sql, array $replacements = array(), $key)
	{
		$data = array();
		$sth = self::prex($sql, $replacements);
		while ($results = $sth->fetch())
		{
			$data[$results[$key]] = $results;
		}

		return $data;
	}

	/**
	 * Fetch results as an associative array
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @param string $sql The SQL query to excecute
	 * @param array $replacements The items to replace the placeholders in the SQL
	 *
	 * @return array MysqlResult The database result object
	 *
	 */
	public function fetch_assoc($sql, array $replacements = array())
	{
		$data = array();
		$sth = self::prex($sql, $replacements);

		$data = $sth->fetch();

		return $data;
	}

	/**
	 * Prepare the SQL statement
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @param type $sql The statement to be prepared
	 * @return string The prepared statement
	 *
	 */
	public function prepare($sql)
	{
		return parent::prepare($sql);
	}

	/**
	 * PREpare and eXcecute (thanks carl(!)) a statement
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @param string $sql The SQL statement to be excecuted
	 * @param array $replacements The replacement variables to be used in prepare
	 * @return MysqlResult
	 */
	public function prex($sql, $replacements = array())
	{
		$this->queries++;
		$query_string = $sql;

		foreach ($replacements as $k => $value)
		{
			$query_string = str_replace($k, $value, $query_string);
		}

		$this->queryList[] = $query_string;
		$sth = $this->prepare($sql);
		$sth->setFetchMode(PDO::FETCH_ASSOC);

		$execute = $sth->execute($replacements);
		if ($execute === false)
		{
			$this->error($sth, $query_string);
			
		}

		return $sth;
	}

	/**
	 * Get the error message
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @param type $sth Statement Handle
	 * @param type $sql SQL String
	 * @return string The error message
	 *
	 */
	public function error($sth, $sql)
	{
		
		exit;
	}

}

/**
 * Database connection object
 *
 * @author Carl Markham <carl@cjmarkham.co.uk>
 * @version 1.0
 * @package Squirrel
 *
 */
class Db
{

	/**
	 * The database object instance
	 *
	 * @access public
	 * @static
	 * @var object
	 *
	 */
	public static $instance;

	/**
	 * The local connection object
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @var object
	 *
	 */
	public static $local;

	/**
	 * The master connection object
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @var object
	 *
	 */
	public static $master;
	private static $connections = array();
	private static $config = array();

	/**
	 * Conntect to the database
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @param array $config The config array
	 * @return type
	 */
	public static function connect(array $config)
	{
		self::$config = &$config;

		self::$instance = new DbInstance($config);
		self::add_connection(self::$instance);
		return self::$instance;
	}

	public static function add_connection(&$db)
	{
		self::$connections[] = &$db;
		if (!self::$master)
		{
			self::$master = &$db;
		}

		self::$local = &$db;
	}

	/**
	 * Escape a string
	 *
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @param string $str The string to be escaped
	 * @return string The escaped string
	 *
	 */
	public static function escape($str)
	{
		return self::$instance->escape($str);
	}

	/**
	 * Fetch one result from the database
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @param string $sql The SQL query to excecute
	 * @param array $replacements The items to replace the placeholders in the SQL
	 *
	 * @return array The data in the first result row
	 *
	 */
	public static function fetch_one($sql, array $replacements)
	{
		return self::$instance->fetch_one($sql, $replacements);
	}

	/**
	 * Fetch all results from the database
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @param string $sql The SQL query to excecute
	 * @param array $replacements The items to replace the placeholders in the SQL
	 *
	 * @return array The data returned from the query
	 *
	 */
	public static function fetch_results($sql, array $replacements)
	{
		return self::$instance->fetch_results($sql, $replacements);
	}

	/**
	 * Fetch results as an associative array
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @param string $sql The SQL query to excecute
	 * @param array $replacements The items to replace the placeholders in the SQL
	 *
	 * @return array MysqlResult The database result object
	 *
	 */
	public static function fetch_assoc($sql, array $replacements)
	{
		return self::$instance->prex($sql, $replacements);
	}

	/**
	 * Prepare and excecute a statement
	 *
	 * @author Carl Markham <carl@cjmarkham.co.uk>
	 * @access public
	 * @static
	 * @param string $sql The SQL statement to be excecuted
	 * @param array $replacements The replacement variables to be used in prepare
	 * @return MysqlResult
	 */
	public static function prex($sql, array $replacements)
	{
		return self::$instance->prex($sql, $replacements);
	}

}