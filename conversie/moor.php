<?php
set_time_limit(300);
date_default_timezone_set("CET");
include(dirname(__FILE__) ."/settings.php");
include("../../AODS/AODS.php");

    class MoorConnect{
        var $json;
        var $licenskey;
        var $ids;
        var $client;
        var $batchsize;
        
        function MoorConnect($licenskey = MOOR_LICENSEKEY){
            $this->licenskey = $licenskey;
            $this->ids = Array();
            $json = Array();
            $json["type"] = "FeatureCollection";
            $json["metadata"] = Array(
                "LastRoadworkUpdatedDate" => null,
                "TotalEntitiesReturned" => 0
                );
            $json["features"] = Array();            
            
            $this->client = new SoapClient("http://miws.gemeenten.opbrekingen.nl/v2012.7.0/RoadworkService.svc?wsdl");
            $this->batchsize = 500;
        }
        
        function getIdsByDate($start, $end){
            $params = new stdClass();

            $params->StartDateFrom = $start;  
            $params->StartDateTo = $end;  
            $params->EndDateFrom = $start;
            $params->EndDateTo = $end;

            $request = new stdClass();
            $request->LicenseKey = $this->licenskey;
            $request->Parameters = $params;

            $result = $this->client->__call('GetRoadworkIds', array(array("request" => $request)));

            foreach($result->GetRoadworkIdsResult->RoadworkIds->RoadworkId as $roadwork){
                $temp = new stdClass();
                $temp->Id = $roadwork->Id;
                $this->ids[] = $temp;
            }
        }
        
        function getRoadworksBatch($arr){
            $request = new stdClass();
            $request->LicenseKey = $this->licenskey;
            $request->RoadworkIds = $arr;

            $result = $this->client->__call('GetRoadworksById', array(array("request" => $request)));
            
            if($result->GetRoadworksByIdResult->ResultCode == "Success"){
                $lastupdate = date("Y-m-d", max(strtotime($this->json["metadata"]["LastRoadworkUpdatedDate"]),strtotime($result->GetRoadworksByIdResult->LastRoadworkUpdatedDate)));
                
                $this->json["type"] = "FeatureCollection";
                $this->json["metadata"]["LastRoadworkUpdatedDate"] = $lastupdate;
                $this->json["metadata"]["TotalEntitiesReturned"] += $result->GetRoadworksByIdResult->TotalEntitiesReturned;
                
                foreach($result->GetRoadworksByIdResult->RoadworkList->RoadworkView as $roadwork){
                    $feature = Array("type" => "Feature");
                    if(is_array($roadwork->XYCoordinates->Coordinates->Coordinate)){
                      $feature["geometry"] = Array("type" => "MultiPoint", "coordinates" => Array());
                      foreach($roadwork->XYCoordinates->Coordinates->Coordinate as $c){
                          $feature["geometry"]["coordinates"][] = Array($c->Longitude, $c->Latitude);
                      }
                    } else {
                      $feature["geometry"] = Array("type" => "Point", "coordinates" => Array($roadwork->XYCoordinates->Coordinates->Coordinate->Longitude,$roadwork->XYCoordinates->Coordinates->Coordinate->Latitude));
                    }
                    unset($roadwork->ForemanContactDetails);
                    unset($roadwork->IsAsphaltRequired);
                    unset($roadwork->IsHomeConnection);
                    unset($roadwork->IsMachineryRequired);
                    $feature["properties"] = (array)$roadwork;
                    
                    $this->json["features"][] = $feature;
                }
            } else {
                print("Status: ". $result->GetRoadworksByIdResult->ResultCode);
            }
        }
        
        function getRoadworks(){
            while(count($this->ids) > 0){
                $batch = array_splice($this->ids,0,$this->batchsize);
                $this->getRoadworksBatch($batch);
            }
        }
        
        function getJSON(){
            return json_encode($this->json);
        }
    }
    
$moor = new MoorConnect(MOOR_LICENSEKEY);

$startdate = date("Y-m-01");
$enddate = date("Y-m-d", strtotime(date("Y-m-d 00:00:00") . ' + 90 day'));

$moor->getIdsByDate($startdate, $enddate);
$moor->getRoadworks();

$f = fopen("data/MoorRoadworks.json");
fwrite($f, $moor->getJSON());
fclose($f);

$AODS = new AODS();
$AODS->upload("data/MoorRoadworks.json");
?>