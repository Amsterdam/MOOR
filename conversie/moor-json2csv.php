<?php
set_time_limit(3600);
ini_set('memory_limit', '256M');

date_default_timezone_set("CET");
error_reporting(E_ALL);

$f = fopen("../data/moor-2010-2016.csv","w");
for($year = 2010; $year <= 2016; $year++){
   for($q = 1; $q <= 4; $q++){
        $data = json_decode(file_get_contents("../data/moor-". $year ."Q". $q .".json"));
        fputcsv($f,[
                "Address",
                "Latitude",
                "Longitude",
                "SubmissionDate" ,
                "CreatedDate" ,
                "StartDate" ,
                "CompletedDate" ,
                "HandOverDate" ,
                "Id" ,
                "KlicNumber" ,
                "PermitNumber" ,
                "ProjectName" ,
                "PublicSpaceManagerOrganisationId" ,
                "PublicSpaceManagerOrganisationName" ,
                "RoadworkDiggerOrganisationId" ,
                "RoadworkDiggerOrganisationName" ,
                "RoadworkOwnerOrganisationId" ,
                "RoadworkOwnerName" ,
                "RoadworkPaverOrganisationId" ,
                "RoadworkPaverOrganisationName" ,
                "RoadworkStatus" ,
                "TrafficNuisanceType" ,
                "WorkType"
            ]);    
            
        foreach($data->features as $feature){
            if(is_array($feature->properties->XYCoordinates->Coordinates->Coordinate)){
                $latitude = $feature->properties->XYCoordinates->Coordinates->Coordinate[0]->Latitude;
                $longitude = $feature->properties->XYCoordinates->Coordinates->Coordinate[0]->Longitude;
            } else {
                $latitude = $feature->properties->XYCoordinates->Coordinates->Coordinate->Latitude;
                $longitude = $feature->properties->XYCoordinates->Coordinates->Coordinate->Longitude;
            }
            
            fputcsv($f,[
                '"'.$feature->properties->Address .'"',
                $latitude,
                $longitude,
                $feature->properties->SubmissionDate ,
                $feature->properties->CreatedDate ,
                $feature->properties->StartDate ,
                $feature->properties->CompletedDate ,
                $feature->properties->HandOverDate ,
                $feature->properties->Id ,
                $feature->properties->KlicNumber ,
                $feature->properties->PermitNumber ,
                $feature->properties->ProjectName ,
                $feature->properties->PublicSpaceManagerOrganisationId ,
                $feature->properties->PublicSpaceManagerOrganisationName ,
                $feature->properties->RoadworkDiggerOrganisationId ,
                $feature->properties->RoadworkDiggerOrganisationName ,
                $feature->properties->RoadworkOwnerOrganisationId ,
                $feature->properties->RoadworkOwnerName ,
                $feature->properties->RoadworkPaverOrganisationId ,
                $feature->properties->RoadworkPaverOrganisationName ,
                $feature->properties->RoadworkStatus ,
                $feature->properties->TrafficNuisanceType ,
                $feature->properties->WorkType
            ]);    
        }
    }
}
fclose($f);   

?>