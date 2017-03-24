<?php

class Conn_global {
	private $_host = '192.168.240.107';
	private $_user = 'dev_dboard';
	private $_pass = 'devdboard';
	private $_db = 'db_MROSystem';

	public function create_connection() {
		$conn_db = mssql_connect($this->_host, $this->_user, $this->_pass);
		$select_db = mssql_select_db($this->_db, $conn_db);

		if(!$conn_db) {
			die('Failed to connect to database server ');
		}
		if(!$select_db) {
			die('Failed to connect to database');
		}
		return $conn_db;
	}
}