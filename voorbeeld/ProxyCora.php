<?php
    function RD2WGS84($x, $y){
        /* Conversie van Rijksdriehoeksmeting naar latitude en longitude (WGS84)
        Voorbeeld: Station Utrecht    
        x = 136013;
        y = 455723;
        */

        $dX = ($x - 155000) * pow(10,-5);
        $dY = ($y - 463000) * pow(10,-5);

        $SomN = (3235.65389 * $dY) + (-32.58297 * pow($dX,2)) + (-0.2475 * pow($dY,2)) + (-0.84978 * pow($dX,2) * $dY) + (-0.0655 * pow($dY,3)) + (-0.01709 * pow($dX,2) * pow($dY,2)) + (-0.00738 * $dX) + (0.0053 * pow($dX,4)) + (-0.00039 * pow($dX,2) * pow($dY,3)) + (0.00033 * pow($dX,4) * $dY) + (-0.00012 * $dX * $dY);
        $SomE = (5260.52916 * $dX) + (105.94684 * $dX * $dY) + (2.45656 * $dX * pow($dY,2)) + (-0.81885 * pow($dX,3)) + (0.05594 * $dX * pow($dY,3)) + (-0.05607 * pow($dX,3) * $dY) + (0.01199 * $dY) + (-0.00256 * pow($dX,3) * pow($dY,2)) + (0.00128 * $dX * pow($dY,4)) + (0.00022 * pow($dY,2)) + (-0.00022 * pow($dX,2)) + (0.00026 * pow($dX,5));

        $lat = 52.15517 + ($SomN / 3600);
        $lon = 5.387206 + ($SomE / 3600);
        
        return(Array($lon, $lat));
    }
    
    function ArrRD2WGS84($arr){
        if(is_array($arr) && is_numeric($arr[0]) && is_numeric($arr[1])){
            if($arr[0] > 360 && $arr[1] > 360){
                return RD2WGS84($arr[0],$arr[1]);
            } else {
                return $arr;
            }
        }elseif(is_array($arr)){
            $temp = Array();
            foreach($arr as $elem){
                $temp[] = ArrRD2WGS84($elem);
            }
            return $temp;
        }
    }
    
    $json = json_decode(file_get_contents("http://open.datapunt.amsterdam.nl/Projecten_Amsterdam_GeoJson.json"));
    
    foreach($json->features as $key => $f){
        $json->features[$key]->geometry->coordinates = ArrRD2WGS84($f->geometry->coordinates);
    }
        
    header('Content-Type: application/json');
    echo json_encode($json);
?>
