<?php
date_default_timezone_set("UTC");
require_once('lib/mq_transactions.php');
$mq = new Lib_mq();

/*
	db connection
*/
$server_db = "192.168.240.100";
$conn_db = mssql_connect($server_db, 'usr-hil', 'passwordhil');
$select_db = mssql_select_db('db_shil', $conn_db);
//end db connection
if(!$conn_db) {
	die('Failed to connect to database server ');
}
if(!$select_db) {
	die('Failed to connect to database');
}

//$query = mssql_query("SELECT * FROM tblHIL_swift WHERE DateClose = '0000-00-00 00:00:00.000'", $conn_db);
$query = mssql_query("SELECT * FROM tblHIL_swift WHERE DateClose = '0000-00-00 00:00:00.000' AND (FlagStatus = 0 OR FlagStatus IS NULL)", $conn_db);
$query_station = mssql_query("SELECT StaID, StaCode FROM tblstation", $conn_db);
while ($st = mssql_fetch_array($query_station)) {
	$station[$st['StaID']] = $st['StaCode'];
}

$seq_number = json_decode(file_get_contents('sequence_mel.json'), TRUE);
$current_seq = $seq_number['sequence'];
if ($current_seq > 99999999) $current_seq = 0;

while($rows = mssql_fetch_array($query)) {	
	$status = 'Open';
	$ct = '';
	if ($rows['Status'] == 3) $status = 'Deleted'; 
	$exp_occur = explode(' ', $rows['DateOccur']);
	$cd = $exp_occur[0];
	if (isset($exp_occur[1])) $ct = $exp_occur[1];
	if (empty($ct)) $ct = '00:00:00.000';
	$logged = $cd . 'T' . $ct . 'Z';
	$stat = '';

	if (isset($station[$rows['StaID']])) {
		$stat = $station[$rows['StaID']];
	}
	
	$desc = str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $rows['Description']);
	$description = preg_replace('/[^A-Za-z0-9\- &;:#\/\\\\.(),$@!*_+=]/', '', $desc);
	$itemID = substr($rows['itemID'], -8);

	$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<ns0:AirOpsDefectAdvice xmlns:ns0="defect.xsd.ops.sabre.com">
				<MessageHeader>
					<MessageCreationDate>'.date('Y-m-d').'Z</MessageCreationDate>
					<MessageCreationTime>'.substr(date('H:i:s.u'), 0, -3).'Z</MessageCreationTime>
					<OriginatingSystem>SWIFT</OriginatingSystem>
					<MessageSequenceNumber>'.convert_sequence($current_seq).'</MessageSequenceNumber>
				</MessageHeader>
				<ReferenceId>'.$rows['TECHLOG'].'</ReferenceId>
				<AircraftRegistration>'.$rows['acreg'].'</AircraftRegistration>
				<DefectGroup>'.$itemID.'</DefectGroup>
				<LoggedDateTime>'.$logged.'</LoggedDateTime>
				<MELReferenceCode>'.convert_ata($rows['ATANo']).'</MELReferenceCode>
				<MELFullReference>'.$rows['DDGRef'].'</MELFullReference>
				<Position/>
				<Description>'.$description.'</Description>
				<Category>'.convert_category($rows['Category']).'</Category>
				<LoggedAirport>'.$stat.'</LoggedAirport>
				<DefectStatus>'.$status.'</DefectStatus>
			</ns0:AirOpsDefectAdvice>';

	$put = $mq->put_queue($xml);

	if ($put) {
		mssql_query("UPDATE tblHIL_swift 
			SET FlagStatus = 1
			WHERE itemID = '".$rows['itemID']."'
		", $conn_db);

		$current_seq++;
	}
}

$last['sequence'] = $current_seq;
file_put_contents('sequence_mel.json', json_encode($last));
//var_dump($last); exit();

function convert_category($id = NULL) {
	$arr = array(
		1 => 'A',
		2 => 'B',
		3 => 'C',
		4 => 'D'
	);
	if (array_key_exists($id, $arr)) return $arr[$id];
	else return 'ID Not Defined';
}

function convert_ata($ata) {
	if ($ata > 0 && $ata <= 9) {
		$f = '0';
	}
	else {
		$f = '';
	}
	return $f . $ata;
}

function convert_sequence($number) { //8 digits
	if ($number >= 0 && $number <=9) {
		$f = '0000000';
	}
	elseif ($number > 9 && $number <= 99) {
		$f = '000000';
	}
	elseif ($number > 99 && $number <=999) {
		$f = '00000';
	}
	elseif ($number > 999 && $number <=9999) {
		$f = '0000';
	}
	elseif ($number > 9999 && $number <=99999) {
		$f = '000';
	}
	elseif ($number > 99999 && $number <= 999999) {
		$f = '00';
	}
	elseif ($number > 999999 && $number <= 9999999) {
		$f = '0';
	}
	else {
		$f = '';
	}
	return $f . $number;
}

