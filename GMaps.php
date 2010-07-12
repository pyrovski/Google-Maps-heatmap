<? require_once("include/functions.inc"); ?>
<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
   <title>Google Maps JavaScript API Example</title>

<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
   <script type="text/javascript">

var myPolygons = [];
var map;
   function initialize() {

     <? 
     if (!isset($db)) {
         $db = dbConnect('localhost', 'GoogleHeatMap');
         mysql_select_db('GoogleHeatMap');
    }

    $mainQuery = "select * from TucsonCrime;";
    $mainResult = mysql_query($mainQuery);
    if (!$mainResult) { echo "Error!"; } 
    else {
        if (isset($db)) { mysql_close($db); unset($db); }
        if (mysql_num_rows($mainResult) > 0) {
            $i = 0;
            while ($row = mysql_fetch_assoc($mainResult)) {
                $allLats[$i] = $row['Latitude'];
                $allLngs[$i] = $row['Longitude'];
		$allDates[$i] = $row['EventDate'];
                $i++;
            }
        }
    }
    
    $minDate = min($allDates);
    $maxDate = max($allDates);

    $meanLat = array_sum($allLats) / sizeof($allLats);
    $meanLng = array_sum($allLngs) / sizeof($allLngs);
    
    if (isset($_GET['Lat'])) {
        $centerLat = $_GET['Lat'];
    } else {
        $centerLat = $meanLat;
    }
    if (isset($_GET['Lng'])) {
        $centerLng = $_GET['Lng'];
    } else {
        $centerLng = $meanLng;
    }
    if (isset($_GET['Zoom'])) {
        $initZoom = $_GET['Zoom'];
    } else {
        $initZoom = 11;
    }


    ?>
     var myLatLng = new google.maps.LatLng(<? echo $centerLat; ?>, <? echo $centerLng; ?>);
     var myOptions = {zoom: <? echo $initZoom; ?>, center: myLatLng, mapTypeId: google.maps.MapTypeId.TERRAIN
        };
     map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    <?

     $varLat = 0;
     $varLng = 0;

     for ($i = 0; $i < sizeof($allLats); $i++) {
       $varLat += pow($allLats[$i]-$meanLat, 2);
       $varLng += pow($allLngs[$i]-$meanLng, 2);
     }

     $varLat /= sizeof($allLats);
     $varLng /= sizeof($allLngs);

     $minLng = $meanLng - 40*$varLat;
     $maxLng = $meanLng + 40*$varLat;
     $maxLat = $meanLat + 40*$varLat;
     $minLat = $meanLat - 40*$varLat;

    if (isset($_GET['Resolution'])) {
        $N = $_GET['Resolution'];
    } else {
     $N = 25;
    }
     for ($i = 0; $i < $N; $i++) {
       for ($j = 0; $j < $N; $j++) {
	 $freqArr[$i][$j] = 0;
	 $lowerLat[$i] = $minLat +     $i*($maxLat-$minLat)/$N;
	 $upperLat[$i] = $minLat + ($i+1)*($maxLat-$minLat)/$N;

	 $lowerLng[$j] = $minLng +     $j*($maxLng-$minLng)/$N;
	 $upperLng[$j] = $minLng + ($j+1)*($maxLng-$minLng)/$N;
       }
     }

     $lngSpan = $maxLng - $minLng;
     $latSpan = $maxLat - $minLat;
     $binLngSpan = $lngSpan / $N;
     $binLatSpan = $latSpan / $N;

     for ($i = 0; $i < sizeof($allLats); $i++) {
       $ybin = intval(($allLngs[$i] - $minLng) / $lngSpan * $N);
       $xbin = intval(($allLats[$i] - $minLat) / $latSpan * $N);
       if($xbin < 0 or $xbin > $N - 1)
	 continue;
       if($ybin < 0 or $ybin > $N - 1)
	 continue;
       $freqArr[$xbin][$ybin]++;
     }

     $maxFreq = 0;
     for ($i = 0; $i < $N; $i++) {
       $maxFreq = max($maxFreq, max($freqArr[$i]));
     }

    $overlayNum = 0;
     for ($i = 0; $i < $N; $i++) {
       for ($j = 0; $j < $N; $j++) {
	 $intensity = $freqArr[$i][$j] / $maxFreq;
	 if ($intensity <= 0.33) {
	   $colorDecimal = (int)(255 * $intensity / 0.33);
	   $colorHex     = base_convert($colorDecimal, 10, 16);
	   if (strlen($colorHex) < 2) {
	     $colorHex = "0".$colorHex;
	   }
	   $color = "#";
	   $color .= (string)$colorHex;
	   $color .= "0000";
	 } else if ($intensity <= 0.66 ) {
	   $colorDecimal = (int)(255 * ($intensity-0.33) / 0.33);
	   $colorHex     = base_convert($colorDecimal, 10, 16);
	   if (strlen($colorHex) < 2) {
	     $colorHex = "0".$colorHex;
	   }
	   $color  = "#ff";
	   $color .= (string)$colorHex;
	   $color .= "00";
	 } else if ($intensity > 0.66) {
	   $colorDecimal = (int)(255 * ($intensity-0.66) / 0.34);
	   $colorHex     = base_convert($colorDecimal, 10, 16);
	   if (strlen($colorHex) < 2) {
	     $colorHex = "0".$colorHex;
	   }
	   $color  = "#ffff";
	   $color .= (string)$colorHex;
	 }

     if (strcmp($color, "#000000") == 0) {
       continue;
     } else {
         if (isset($_GET['Opacity'])) {
             $opacity = $_GET['Opacity'];
        } else {
         $opacity = 0.8;
        }
     }

	 ?>
     boxCoords = [new google.maps.LatLng(<? echo $lowerLat[$i].",".$lowerLng[$j]; ?>),
                      new google.maps.LatLng(<? echo $lowerLat[$i].",".$upperLng[$j]; ?>),
                      new google.maps.LatLng(<? echo $upperLat[$i].",".$upperLng[$j]; ?>),
                      new google.maps.LatLng(<? echo $upperLat[$i].",".$lowerLng[$j]; ?>)];

	 myPolygons[<? echo $overlayNum; ?>] = new google.maps.Polygon({
            paths: boxCoords,
            strokeColor: "#000000",
            strokeOpacity: 0.0,
            strokeWeight: 0,
            fillColor: "<? echo $color; ?>",
            fillOpacity: <? echo $opacity; ?>});
     myPolygons[<? echo $overlayNum; ?>].setMap(map);
	 <?  
        $overlayNum++;
       }
     }

   ?>


}

function changeOpacity(newOpacity) {
  for (i = 0; i < <? echo $overlayNum; ?>; i++) {
    myPolygons[i].setOptions({fillOpacity: newOpacity});
  }
}

function changeResolution(newResolution, newOpacity) {
    if (newResolution != <? echo $N; ?>) {
        window.location.href="GMaps.php?Resolution="+newResolution+
                            "&Opacity="+newOpacity+
                            "&Lat="+map.getCenter().lat()+
                            "&Lng="+map.getCenter().lng()+
                            "&Zoom="+map.getZoom();
    }
}

function addAddress() {
    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById("address").value;
    if (geocoder) {
        geocoder.geocode({ 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var marker = new google.maps.Marker({
                    map: map,
                    position: results[0].geometry.location});
            }
        });
    }
}
</script>
</head>
<body onload="initialize()" onunload="GUnload()">
    <div align=center>
    <div style="margin-bottom: 10px;">
        Location: <input type="text" name="address" 
                         id="address"
                         onChange="addAddress()"/>
                  <input type="button" value="Drop Pin" onClick=addAddress()"/>
    </div>

    <div id="map_canvas" style="width: 800px; height: 600px"></div>
    <div>
        <form name="input" action="GMaps.php" method="get">
        Resolution: <input type="range" name="Resolution" 
                            min="1" max="100" step="5" 
                            value="<? echo $N; ?>" 
                            onMouseUp="changeResolution(
                            this.value,getElementsByName('Opacity')[0].value)" />
        Opacity: <input type="range" name="Opacity" 
                            min="0" max="1.0" step="0.01" 
                            value="<? echo $opacity; ?>" 
                            onChange="changeOpacity(this.value)" />
        </form>
    </div>
  <div style="width: 800px">This map shows police incident density in Tucson over 
  the period beginning <? echo $minDate ?> and ending <? echo $maxDate ?>.  
  Data is currently sourced from 
  <a href="http://maps.azstarnet.com/crime/show30">the AZ Star website</a>, 
  because the Tucson police department is reluctant to interface with the 
  unwashed masses of the World Wide Web.</div>
<br><br>
</div>
</body>
</html>
