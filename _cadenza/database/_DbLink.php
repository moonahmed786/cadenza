<?php
class DbLink {
	
	static $dbh = null;
	static $connected = false;
	
	/**
	 * Creates a new PDO object and returns it.
	 * 
	 * @return PDO object
	 */
	static function create() {
		$config = Core::cadenzaConfig();
		$dsn = 'mysql:host='.$config['mysql_host'].';dbname='.$config['mysql_database'].';charset=utf8';
		$username = $config['mysql_username'];
		$password = $config['mysql_password'];
		try {
			static::$dbh = new PDO($dsn, $username, $password);
		}
		catch (PDOException $e) {
			die('Connection failed: '.$e->getMessage());
		}
		static::$connected = true;
		return static::$dbh;
	}
	
	/**
	 * If already connected, returns existing PDO object;
	 * otherwise, creates a new PDO object and returns it.
	 * 
	 * @return PDO object
	 */
	static function get() {
		if (static::$connected) {
			return static::$dbh;
		}
		return static::create();
	}
	
	/**
	 * Delegate the given parameters to the PDO object in order
	 * to prepare a statement and return a statement object.
	 * Will attempt to establish connection if not already connected.
	 * 
	 * @return PDOStatement object
	 */
	static function prepare($statement, $driver_options=array()) {
		return static::get()->prepare($statement, $driver_options);
	}
	
	/**
	 * Delegate to the PDO object in order to return the ID of the
	 * last inserted row.
	 * 
	 * @return string
	 */
	static function lastInsertId() {
		return static::get()->lastInsertId();
	}
	
	/**
	 * If connected, close the connection.
	 */
	static function close() {
		if (static::$connected) {
			static::$dbh = null;
			static::$connected = false;
		}
	}
}