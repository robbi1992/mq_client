<?php
date_default_timezone_set("UTC");
require_once('lib/mq_transactions.php');
$mq = new Lib_mq('TEST.REVISION.QUEUE');
/*
	db connection
*/
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

//$query = mssql_query("SELECT * FROM TBL_SABRE_REVISION WHERE SEND_FLAG = 0 OR SEND_FLAG IS NULL", $conn_db);
$query = mssql_query("SELECT TIMEZ, AC_REG, REV_NO, REV_DESC, REV_TYP,  PLAN_START_DATE, PLAN_START_TIME, ACT_START_DATE, ACT_START_TIME, SCHED_SRV_DATE, SCHED_SRV_TIME, ACT_SRV_DATE, ACT_SRV_TIME, WRKCTR_CODE, LOC_TYP, ASSGN_AIRPORT_CODE, DELETED FROM TBL_SABRE_REVISION WHERE SEND_FLAG = 0 OR SEND_FLAG IS NULL", $conn_db);

$seq_number = json_decode(file_get_contents('sequence.json'), TRUE);
$current_seq = $seq_number['sequence'];
if ($current_seq > 99999999) $current_seq = 0;

/* json revision log*/
$rev_log = json_decode(file_get_contents('revision_log.json'), TRUE);

while($rows = mssql_fetch_array($query)) {
	$index = $rows['TIMEZ'] . $rows['AC_REG']  . $rows['REV_NO'] . $rows['REV_DESC'] . $rows['REV_TYP'] . $rows['PLAN_START_DATE'] . $rows['PLAN_START_TIME'] . $rows['ACT_START_DATE'] . $rows['ACT_START_TIME'] . $rows['SCHED_SRV_DATE'] . $rows['SCHED_SRV_TIME'] . $rows['ACT_SRV_DATE'] . $rows['ACT_SRV_TIME'] . $rows['WRKCTR_CODE'] . $rows['LOC_TYP'] . $rows['ASSGN_AIRPORT_CODE'] . $rows['DELETED'];
	if ( ! isset($rev_log[$index])) {
			
		$utc = $rows['TIMEZ'];
		$acreg = '<AircraftRegistration>'.$rows['AC_REG'].'</AircraftRegistration>';
		$revNo = '<RevisionNumber>'.$rows['REV_NO'].'</RevisionNumber>';
		$revDesc = '<RevisionDescription>'.$rows['REV_DESC'].'</RevisionDescription>';
		$typeCode = '<MaintenanceActivityTypeCode>'.type_code($rows['REV_TYP']).'</MaintenanceActivityTypeCode>';
		$genPlan = utc_converter($rows['PLAN_START_DATE'], $rows['PLAN_START_TIME'], $utc);
		$plan = '<PlannedRevisionStartDateTime>'.$genPlan.'</PlannedRevisionStartDateTime>';
		$genAct = utc_converter($rows['ACT_START_DATE'], $rows['ACT_START_TIME'], $utc);
		$act = '<ActualRevisionStartDateTime>'.$genAct.'</ActualRevisionStartDateTime>';
		$genSched = utc_converter($rows['SCHED_SRV_DATE'], $rows['SCHED_SRV_TIME'], $utc);
		$sched = '<ScheduledServiceabilityDateTime>'.$genSched.'</ScheduledServiceabilityDateTime>';
		$genActSrv = utc_converter($rows['ACT_SRV_DATE'], $rows['ACT_SRV_TIME'], $utc);
		$actSrv = '<ActualServiceabilityDateTime>'.$genActSrv.'</ActualServiceabilityDateTime>';
		$workCode = '<WorkCentreCode>'.$rows['WRKCTR_CODE'].'</WorkCentreCode>';
		$locType = '<LocationType>'.$rows['LOC_TYP'].'</LocationType>';
		$assignCode = '<AssignedAirportCode>'.$rows['ASSGN_AIRPORT_CODE'].'</AssignedAirportCode>';
		$deleted = '<Deleted>'.$rows['DELETED'].'</Deleted>';

		if (empty($rows['AC_REG'])) $acreg = '<AircraftRegistration xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($rows['REV_NO'])) $revNo = '<RevisionNumber xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($rows['REV_DESC'])) $revDesc = '<RevisionDescription xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($rows['REV_TYP'])) $typeCode = '<MaintenanceActivityTypeCode xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($genPlan)) {
			$plan = '<PlannedRevisionStartDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		}

		// new logic important!
		$estService = '<EstimatedServiceabilityDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($genSched)) $sched = '<ScheduledServiceabilityDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($genAct)) {
			$act = '<ActualRevisionStartDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>'; // starter 	condition
			
			// second condition
			/*if (isset($revision_log[$rows['REV_NO']])) {
				$sched = '<ScheduledServiceabilityDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
				$estService = '<EstimatedServiceabilityDateTime>'.$genSched.'</EstimatedServiceabilityDateTime>';
				
				unset($revision_log[$rows['REV_NO']]);
			}*/
		}
		else {
			$sched = '<ScheduledServiceabilityDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
			$estService = '<EstimatedServiceabilityDateTime>'.$genSched.'</EstimatedServiceabilityDateTime>';
			
			//$revision_log[$rows['REV_NO']] = $genAct;
		}

		if (empty($genActSrv)) $actSrv = '<ActualServiceabilityDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($rows['WRKCTR_CODE'])) $workCode = '<WorkCentreCode xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($rows['LOC_TYP'])) $locType = '<LocationType xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($rows['ASSGN_AIRPORT_CODE'])) $assignCode = '<AssignedAirportCode xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';
		if (empty($rows['DELETED'])) $deleted = '<Deleted xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>';

		$xml = '';
		$xml .= '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<rev:RevisionsMessage xmlns:rev="http://www.sabre.com/schema/airops/RevisionsMessage">';
		$xml .= '<MessageHeader>
				<MessageCreationDate>'.date('Y-m-d').'Z</MessageCreationDate>
				<MessageCreationTime>'.substr(date('H:i:s.u'), 0, -3).'Z</MessageCreationTime>
				<OriginatingSystem>SWIFT</OriginatingSystem>
				<MessageSequenceNumber>'.convert_sequence($current_seq).'</MessageSequenceNumber>
			</MessageHeader>';
		$xml .= $acreg;
		$xml .= $revNo;
		$xml .= $revDesc;
		$xml .= $typeCode;
		$xml .= '<RevisionPreTime>0030</RevisionPreTime>
			<RevisionPostTime>0030</RevisionPostTime>'; // default
		$xml .= $plan;
		$xml .= '<EstimatedRevisionStartDateTime xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>'; //default
		$xml .= $act;
		$xml .= $sched;
		$xml .= '<EstimatedServiceabilityDetails>';
		$xml .= $estService;
		$xml .= '<EstimatedServiceabilityStatusCode>E</EstimatedServiceabilityStatusCode>';
		$xml .= '</EstimatedServiceabilityDetails>';
		$xml .= $actSrv;
		$xml .= '<ServiceabilityCode>A</ServiceabilityCode>';
		$xml .= $workCode;
		$xml .= $locType;
		$xml .= $assignCode;
		$xml .= $deleted;
		$xml .= '</rev:RevisionsMessage>'; //the last one

		$put = $mq->put_queue($xml);

		//if mq success put on mq client asyst
		if ($put) {
			mssql_query("UPDATE TBL_SABRE_REVISION SET SEND_FLAG = 1 WHERE REV_NO = '".$rows['REV_NO']."'", $conn_db);
			
			$current_seq++;
			$rev_log[$index] = '';
		}
		
	} //end logic (input to mq)
	//break;
	//var_dump($xml); exit();
}

$last['sequence'] = $current_seq;
file_put_contents('sequence.json', json_encode($last));
file_put_contents('revision_log.json', json_encode($rev_log));

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

function join_date_time($a, $b) {
	if (empty($a) || empty($b)) return '';
	$first = substr_replace($a, '-', 4, 0);
	$second = substr_replace($first, '-', 7, 0);
	$tim = substr_replace($b, ':', 2, 0);
	$time = substr_replace($tim, ':', 5, 0);
	$joined = $second . 'T' . $time . '.000Z';
	return $joined;
}
function utc_converter($a, $b, $utc) {
	if (empty($a) || empty($b) || $a ==  '(NULL)' || $b == '(NULL)') return '';
	$first = substr_replace($a, '-', 4, 0);
	$date = substr_replace($first, '-', 7, 0);
	$tim = substr_replace($b, ':', 2, 0);
	$time = substr_replace($tim, ':', 5, 0);
	
	if (empty($utc) || is_null($utc)) {
		$newDate = $date;
		$newTime = $time;
	}
	else {
		$datetime = $date . ' ' . $time;
		$operator = substr($utc, 3, 1);
		$value = substr($utc, 4, 1);
		if ($operator == '+') {
			$a = strtotime($datetime) - ($value * 60 * 60);
		} 
		elseif ($operator == '-') {
			$a = strtotime($datetime) + ($value * 60 * 60);
		}
		
		$newDate = date('Y-m-d', $a);
		$newTime = date('H:i:s', $a);
	}
	$joined = $newDate . 'T' . $newTime . '.000Z';
	return $joined;
}

function type_code($param = '') {
	if ($param == 8 || $param == 9 || $param == 'D' || $param == 'N' || $param == 'O' || $param == 'P' || $param == 'R' || $param == 'U' || $param == 'X') $result = 'UNSCHED';
	elseif ($param == 1) $result = 'DAILY';
	elseif ($param == 2) $result = 'SERVICE';
	elseif ($param == 3) $result = 'WEEKLY';
	elseif ($param == 4 || $param == 5) $result = 'A CHECK';
	elseif ($param == 6) $result = 'C CHECK';
	elseif ($param == 7) $result = 'D CHECK';
	elseif ($param == 'C') $result = 'AOG';
	else $result = '';
	return $result;
}
