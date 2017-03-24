<?php
$server_db = "192.168.240.107";
$conn_db = mssql_connect($server_db, 'dev_dboard', 'devdboard');
$select_db = mssql_select_db('db_MROSystem', $conn_db);
//end db connection

if(!$conn_db) {
	die('Failed to connect to database server ');
}
if(!$select_db) {
	die('Failed to connect to database');
}

$rev_log = json_decode(file_get_contents('revision_log.json'), TRUE);
$query = mssql_query("SELECT * FROM TBL_SABRE_REVISION WHERE SEND_FLAG = 0 OR SEND_FLAG IS NULL", $conn_db);

while($rows = mssql_fetch_array($query)) {
	$revNo = $rows['REV_NO'];
	$index = $rows['TIMEZ'] . $rows['AC_REG'] . $rows['REV_DESC'] . $rows['REV_TYP'] . $rows['PLAN_START_DATE'] . $rows['PLAN_START_TIME'] . $rows['ACT_START_DATE'] . $rows['ACT_START_TIME'] . $rows['SCHED_SRV_DATE'] . $rows['SCHED_SRV_TIME'] . $rows['ACT_SRV_DATE'] . $rows['ACT_SRV_TIME'] . $rows['WRKCTR_CODE'] . $rows['LOC_TYP'] . $rows['ASSGN_AIRPORT_CODE'] . $rows['DELETED']; 
	if (isset($rev_log[$revNo])) {
		if ($rev_log[$revNo] !== $index) {
			//do something here

			//update log
			$rev_log[$revNo] = $index;
		}
		else {
			//do nothing
			echo 'ok';
		}	
	}
	else {
		//do something here
		echo 'ok';
	}
}

file_put_contents('revision_log.json', json_encode($rev_log));

