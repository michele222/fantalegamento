<?php
require_once 'include/db.php';

class db
{
	private $sql;
	
	function __construct()
	{
		$this->sql = new mysqli(_DBHOST,_DBUSER,_DBPWD,_DBNAME);
		if (mysqli_connect_error())
			return 0;
		if (!$this->query("SET CHARACTER SET utf8"))
			return 0;
		if (!$this->query("SET NAMES utf8"))
			return 0;
		return 1;
	}
	
	public function close()
	{
		return $this->sql->close();
	}
	
	public function query($text)
	{
		if (substr($text,0,3) != 'SET')
		{
			$log = fopen(_LOGFILE,'a');
			fwrite($log,date("Y-m-d H:i:s").', db->query: '.$text."\r\n");
			fclose($log);
		}
		return $this->sql->query($text);
	}
	
	public function begin()
	{
		$this->sql->autocommit(false);
	}
	
	public function commit()
	{
		$this->sql->commit();
		$this->sql->autocommit(true);
	}
	
	public function rollback()
	{
		$this->sql->rollback();
	}
	
	public function lastId()
	{
		return $this->sql->insert_id;
	}
	
	public function affectedRows()
	{
		return $this->sql->affected_rows;
	}
	
	public function escape($text)
	{
		return $this->sql->real_escape_string($text);
	}
}
?>