<?php
date_default_timezone_set("UTC");
require_once('lib/mq_transactions.php');
$mq = new Lib_mq();

$val = 'xml4ops/58d30067e3e3d.xml';
$get = file_get_contents($val);

//$get = $mq->get_queue();
//var_dump($get); exit();
/*
	db connection
*/

$server_db = "192.168.240.107";
$conn_db = mssql_connect($server_db, 'dev_dboard', 'devdboard');
$select_db = mssql_select_db('db_MROSystem', $conn_db);

if(!$conn_db) {
	die('Failed to connect to database server ');
}
if(!$select_db) {
	die('Failed to connect to database');
}

		$xml = simplexml_load_string($get);
		if ($xml === false) {
			echo "Failed loading XML: ";
			foreach(libxml_get_errors() as $error) {
				echo "<br>", $error->message;
			}
			//echo 'file ' . $onlyName . 'doesn\'t have xml format, file is deleted';
			//unlink($val);
		} 
		else {
			$data['carrier'] = $xml->{'FlightLeg.OPS'}->FlightId->Carrier[0];
			$data['flightNumber'] = $xml->{'FlightLeg.OPS'}->FlightId->FlightNumber[0];
			$data['departureAirport'] = $xml->{'FlightLeg.OPS'}->FlightLegId->DepartureAirport[0];
			$date = $xml->{'FlightLeg.OPS'}->FlightId->Date[0];
			$newDate = explode('T', $date);
			$data['date']['theDate'] = $newDate[0];
			//$newTime = explode('.', $newDate[1]);
			//$data['date']['theTime'] = $newTime[0];
			$data['acRegistration'] = $xml->{'FlightLeg.OPS'}->Equipment->AircraftRegistration[0];
			
			//actual	
			if (isset($xml->{'FlightLeg.OPS'}->Time->Actual->BlockOff[0])) {
				$actualBlockOff = $xml->{'FlightLeg.OPS'}->Time->Actual->BlockOff[0];
				$newActualBlockOff = explode('T', $actualBlockOff);
				$data['actualBlockOff']['theDate'] = $newActualBlockOff[0];
				$newTimeActualBlockOff = explode('.', $newActualBlockOff[1]);
				$data['actualBlockOff']['theTime'] = $newTimeActualBlockOff[0];
			}
			else {
				$data['actualBlockOff']['theDate'] = NULL;
				$data['actualBlockOff']['theTime'] = NULL;
			}
			if (isset($xml->{'FlightLeg.OPS'}->Time->Actual->TakeOff[0])) {
				$actualTakeOff = $xml->{'FlightLeg.OPS'}->Time->Actual->TakeOff[0];
				$newActualTakeOff = explode('T', $actualTakeOff);
				$data['actualTakeOff']['theDate'] = $newActualTakeOff[0];
				$newTimeActualTakeOff = explode('.', $newActualTakeOff[1]);
				$data['actualTakeOff']['theTime'] = $newTimeActualTakeOff[0];
			}
			else {
				$data['actualTakeOff']['theDate'] = '';
				$data['actualTakeOff']['theTime'] = '';
			}
	
			if (isset($xml->{'FlightLeg.OPS'}->Time->Actual->TouchDown[0])) {
				$actualTouchDown = $xml->{'FlightLeg.OPS'}->Time->Actual->TouchDown[0];
				$newActualTouchDown = explode('T', $actualTouchDown);
				$data['actualTouchDown']['theDate'] = $newActualTouchDown[0];
				$newTimeActualTouchDown = explode('.', $newActualTouchDown[1]);
				$data['actualTouchDown']['theTime'] = $newTimeActualTouchDown[0];
			}
			else {
				$data['actualTouchDown']['theDate'] = '';
				$data['actualTouchDown']['theTime'] = '';	
			}
		
			if (isset($xml->{'FlightLeg.OPS'}->Time->Actual->BlockOn[0])) {
				$actualBlockOn = $xml->{'FlightLeg.OPS'}->Time->Actual->BlockOn[0];
				$newActualBlockOn = explode('T', $actualBlockOn);
				$data['actualBlockOn']['theDate'] = $newActualBlockOn[0];
				$newTimeActualBlockOn = explode('.', $newActualBlockOn[1]);
				$data['actualBlockOn']['theTime'] = $newTimeActualBlockOn[0];
			}
			else {
				$data['actualBlockOn']['theDate'] = '';
				$data['actualBlockOn']['theTime'] = '';	
			}
			//end actual
		
			//estimated (changed structurre) block off from schedlude -> departure
			if (isset($xml->{'FlightLeg.OPS'}->Time->Scheduled->Departure[0])) {
				$estBlockOff = $xml->{'FlightLeg.OPS'}->Time->Scheduled->Departure[0];
				$newEstBlockOff = explode('T', $estBlockOff);
				$data['estBlockOff']['theDate'] = $newEstBlockOff[0];
				$newTimeEstBlockOff = explode('.', $newEstBlockOff[1]);
				$data['estBlockOff']['theTime'] = $newTimeEstBlockOff[0];
			}
			else {
				$data['estBlockOff']['theDate'] = '';
				$data['estBlockOff']['theTime'] = '';	
			}
			
			if (isset($xml->{'FlightLeg.OPS'}->Time->Estimated->TakeOff[0])) {
				$estTakeOff = $xml->{'FlightLeg.OPS'}->Time->Estimated->TakeOff[0];
				$newEstTakeOff = explode('T', $estTakeOff);
				$data['estTakeOff']['theDate'] = $newEstTakeOff[0];
				$newTimeEstTakeOff = explode('.', $newEstTakeOff[1]);
				$data['estTakeOff']['theTime'] = $newTimeEstTakeOff[0];
			}
			else {
				$data['estTakeOff']['theDate'] = '';
				$data['estTakeOff']['theTime'] = '';	
			}
		
			if (isset($xml->{'FlightLeg.OPS'}->Time->Estimated->TouchDown[0])) {
				$estTouchDown = $xml->{'FlightLeg.OPS'}->Time->Estimated->TouchDown[0];
				$newEstTouchDown = explode('T', $estTouchDown);
				$data['estTouchDown']['theDate'] = $newEstTouchDown[0];
				$newTimeEstTouchDown = explode('.', $newEstTouchDown[1]);
				$data['estTouchDown']['theTime'] = $newTimeEstTouchDown[0];
			}
			else {
				$data['estTouchDown']['theDate'] = '';
				$data['estTouchDown']['theTime'] = '';	
			}
			//here changed to get data from scheduled arrival
			if (isset($xml->{'FlightLeg.OPS'}->Time->Scheduled->Arrival[0])) {
				$estBlockOn = $xml->{'FlightLeg.OPS'}->Time->Scheduled->Arrival[0];
				$newEstBlockOn = explode('T', $estBlockOn);
				$data['estBlockOn']['theDate'] = $newEstBlockOn[0];
				$newTimeEstBlockOn = explode('.', $newEstBlockOn[1]);
				$data['estBlockOn']['theTime'] = $newTimeEstBlockOn[0];
			}
			else {
				$data['estBlockOn']['theDate'] = '';
				$data['estBlockOn']['theTime'] = '';	
			}
		
		
			$data['latestArrival'] = $xml->{'FlightLeg.OPS'}->Airports->LatestArrival[0];
			$data['srvType'] = $xml->{'FlightLeg.OPS'}->ServiceType[0];
			$status = strtolower($xml->{'FlightLeg.OPS'}->Status[0]);
			$data['status'] = '';
			if($status == 'cancel') $data['status'] = 'X';
		
			//timestampt
			$ts = $xml->MessageReference->TimeStamp[0];
			$newTs = explode('T', $ts);
			$data['ts']['theDate'] = $newTs[0];
			$newTimeTs = explode('.', $newTs[1]);
			$data['ts']['theTime'] = $newTimeTs[0];
			//print_r($data); exit();
			$cdn =  strtolower($xml->{'FlightLeg.OPS'}->SpecialStatus[0]);
			$col_depart_no = '01';
			if ($cdn == 'groundreturn' || $cdn == 'airreturn') $col_depart_no = '02';
	
			$col_dupl = $data['carrier'] . '|' . $data['flightNumber'] . '|' . $col_depart_no . '|' . $data['departureAirport'] . '|' . $data['date']['theDate'] . '|' . $data['latestArrival'];
			$col_key = $data['carrier'] . '|' . $data['flightNumber'] . '|' . $col_depart_no . '|' . $data['departureAirport'] . '|' . $data['date']['theDate'] . '|' . $data['acRegistration'] . '|' .
				$data['actualBlockOff']['theDate'] . '|' . $data['actualBlockOff']['theTime'] . '|' . $data['actualTakeOff']['theDate'] . '|' . $data['actualTakeOff']['theTime'] . '|' . 
				$data['estBlockOff']['theDate'] . '|' . $data['estBlockOff']['theTime'] . '|' . $data['estTakeOff']['theDate'] . '|' . $data['estTakeOff']['theTime'] . '|' . $data['actualBlockOn']['theDate'] . '|' .
				$data['actualBlockOn']['theTime'] . '|' . $data['actualTouchDown']['theDate'] . '|' . $data['actualTouchDown']['theTime'] . '|' . $data['estBlockOn']['theDate'] . '|' . $data['estBlockOn']['theTime'] . '|' .
				$data['estTouchDown']['theDate'] . '|' . $data['estTouchDown']['theTime'] . '|' . $data['latestArrival'] . '|' . $data['srvType'];
			
			//dont allow duplicate data
			$query_check = mssql_query("SELECT COL_KEY FROM dbo.TBL_AC_MOVEMENT_PROD1 WHERE CONVERT(VARCHAR(MAX), COL_KEY) = '" . $col_key . "'", $conn_db);
			//$check = mssql_query($query_check, $conn);
			$rows = 0;
			while ($val_rows = mssql_fetch_array($query_check)) {
				$rows++;
			}
			//echo $rows; exit();
			if ($rows == 0) {
				$query = "INSERT INTO dbo.TBL_AC_MOVEMENT_PROD1 (COL_CARRIER_CODE, COL_FLIGHT_NUMBER, COL_DEPARTURE_NUMBER, COL_DEPARTURE_STATION, COL_PLAN_DEPARTURE_DATE,
						COL_AIRCRAFT_REGISTRATION, COL_CHOX_OFF_DATE, COL_CHOX_OFF_TIME, COL_WHEELS_OFF_DATE, COL_WHEELS_OFF_TIME,
						COL_EST_DEP_DATE, COL_EST_DEP_TIME, COL_EST_WHEELS_OFF_DATE, COL_EST_WHEELS_OFF_TIME, COL_CHOX_ON_DATE,
						COL_CHOX_ON_TIME, COL_WHEELS_ON_DATE, COL_WHEELS_ON_TIME, COL_EST_ARR_DATE, COL_EST_ARR_TIME,
						COL_EST_WHEELS_ON_DATE, COL_EST_WHEELS_ON_TIME, COL_ARR_STATION, COL_FLIGHT_TYPE, COL_ENTRY_DATE,
						COL_CANCEL_INDICATOR, COL_DUPL, COL_KEY, COL_FLAG)
					VALUES ('".$data['carrier']."', '".$data['flightNumber']."', '".$col_depart_no."', '".$data['departureAirport']."', '".$data['date']['theDate']."',
						'".$data['acRegistration']."', '".$data['actualBlockOff']['theDate']."', '".$data['actualBlockOff']['theTime']."', '".$data['actualTakeOff']['theDate']."', '".$data['actualTakeOff']['theTime']."',
						'".$data['estBlockOff']['theDate']."', '".$data['estBlockOff']['theTime']."', '".$data['estTakeOff']['theDate']."', '".$data['estTakeOff']['theTime']."', '".$data['actualBlockOn']['theDate']."',
						'".$data['actualBlockOn']['theTime']."', '".$data['actualTouchDown']['theDate']."', '".$data['actualTouchDown']['theTime']."', '".$data['estBlockOn']['theDate']."', '".$data['estBlockOn']['theTime']."',
						'".$data['estTouchDown']['theDate']."', '".$data['estTouchDown']['theTime']."', '".$data['latestArrival']."', '".$data['srvType']."', '".$data['ts']['theDate']. ' ' .$data['ts']['theTime']."',
						'".$data['status']."', '".$col_dupl."', '".$col_key."', '0'
					)
				";
				$insert = mssql_query($query, $conn_db);
				if($insert) {
					//var_dump($get);
					//echo 'success: 1 rows affected from file ' . $onlyName . '<br>';
					//rename($val, $dir_move . $onlyName);
					echo 'success: 1 rows affected';
				}
				else {
					//validation if it doesnt inserted to db
					$xml = new SimpleXMLElement($get);
					$xml_file = $xml->asXML('./xml4ops/' . uniqid('gmf_') . '.xml');
					echo 'error: insert failed';
					//echo 'Error: insert is failed from file ' . $onlyName . '<br>';
				}
			}
			else {
				echo 'notice: Duplicate data';
				//echo 'Notice: Duplicate data from file ' . $onlyName . '<br> File was delete from server';
				//unlink($val);
			}
			//end duplicate
		} // end else

