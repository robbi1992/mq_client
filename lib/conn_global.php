<?php

class Conn_global {
	private $_host = 'mssql-01.gmf-aeroasia.co.id';
	private $_user = 'usr-swf';
	private $_pass = 'p@ssw0rd';
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