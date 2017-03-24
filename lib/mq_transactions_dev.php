<?php

class Lib_mq {

	private $_qm = '';
	private $_channel = '';
	private $_host = '';
	private $_qget = '';
	private $_qput = '';
	private $_allocated_length = NULL;
	private $_object = '';
	
	function __construct($put_queue = 'TEST.MELADVC.QUEUE') {
		$this->_qm = 'GARUDAUAT.QM';
		$this->_channel = 'SWIFTUAT.SVRCONN';
		$this->_host = '192.168.127.80';
		$this->_qget = 'TEST.SWIFT.QUEUE';
		$this->_qput = $put_queue;
		$this->_allocated_length = 500000;
	}

	private function get_options() {
		$options = array(
			'Version' => MQSERIES_MQCNO_VERSION_2,
			'Options' => MQSERIES_MQCNO_STANDARD_BINDING,
			'MQCD' => array('ChannelName' => $this->_channel,
				'ConnectionName' => $this->_host,
				'TransportType' => MQSERIES_MQXPT_TCP
			)
		);
		return $options;
	}

	private function create_connection() {
		mqseries_connx($this->_qm, $this->get_options(), $connection, $return_code, $return_msg);
		if ($return_code !== MQSERIES_MQCC_OK) {
			echo 'error code: ' . $return_code . ', reason:' . mqseries_strerror($return_msg);
			exit();
		}

		return $connection;
	}

	private function close_connection($conn, $obj, $str = MQSERIES_MQCO_NONE) {
		mqseries_close($conn, $obj, $str, $return_code, $return_msg);
	}

	private function set_object($obj) {
		$this->_object = $obj;
	}

	private function get_object() {
		$object = array(
			'ObjectName' => $this->_object,
			'ObjectQMgrName' => $this->_qm
		);
		return $object;
	}

	private function open_connection($conn, $object) {
		$option = MQSERIES_MQOO_INPUT_AS_Q_DEF | MQSERIES_MQOO_FAIL_IF_QUIESCING | MQSERIES_MQOO_OUTPUT;
		mqseries_open($conn, $object, $option, $obj, $return_code, $return_msg);

		if ($return_code !== MQSERIES_MQCC_OK) {
			echo 'error code: ' . $return_code . ', reason:' . mqseries_strerror($return_msg);
			exit();
		}
		return $obj;
	}

	public function get_queue() {
		$connection = $this->create_connection();
		$this->set_object($this->_qget);
		$object = $this->get_object();
		$obj = $this->open_connection($connection, $object);

		$mdg = array();
		$gmo = array('Options' => MQSERIES_MQGMO_FAIL_IF_QUIESCING | MQSERIES_MQGMO_WAIT | MQSERIES_MQGMO_CONVERT, 'WaitInterval' => 3000);

		mqseries_get(
			$connection, $obj, $mdg, $gmo, $this->_allocated_length, $msg, $data_length, $return_code, $return_msg
		);

		if ($return_code !== MQSERIES_MQCC_OK) {
			echo 'error code: ' . $return_code . ', reason:' . mqseries_strerror($return_msg);
			exit();
		}

		return $msg;
		$this->close_connection($connection, $obj);
	}

	public function put_queue($message) {
		$connection = $this->create_connection();
		$this->set_object($this->_qput);
		$object = $this->get_object();
		$obj = $this->open_connection($connection, $object);

		$md = 	array(
			'Version' => MQSERIES_MQMD_VERSION_1,
			'Expiry' => MQSERIES_MQEI_UNLIMITED,
			'Report' => MQSERIES_MQRO_NONE,
			'MsgType' => MQSERIES_MQMT_DATAGRAM,
			'Format' => MQSERIES_MQFMT_STRING,
			'Priority' => 1,
			'Persistence' => MQSERIES_MQPER_PERSISTENT
		);

		$pmo = array('Options' => MQSERIES_MQPMO_NEW_MSG_ID);
		mqseries_put(
			$connection, $obj, $md, $pmo, $message, $return_code, $return_msg
		);

		if ($return_code !== MQSERIES_MQCC_OK) {
			echo 'error code: ' . $return_code . ', reason:' . mqseries_strerror($return_msg);
			exit();
		}
		
		return 'put message to qm success';
	}
}