<?php

$get = file_get_contents('xml4ops.xml');
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
		
			//estimated
			if (isset($xml->{'FlightLeg.OPS'}->Time->Estimated->BlockOff[0])) {
				$estBlockOff = $xml->{'FlightLeg.OPS'}->Time->Estimated->BlockOff[0];
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
		
			if (isset($xml->{'FlightLeg.OPS'}->Time->Estimated->BlockOn[0])) {
				$estBlockOn = $xml->{'FlightLeg.OPS'}->Time->Estimated->BlockOn[0];
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
			print_r($data); exit();
		}