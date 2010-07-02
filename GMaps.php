<? require_once("include/functions.inc"); ?>
<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
   <title>Google Maps JavaScript API Example</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=ABQIAAAAgpR_fTVDfgW2Ih1y8ZHGbxSTnBdCdbPgGylcVq1ApFx0qTYq3RTkQ0EwgJVnNgy_mJV4H5dSqzw2Pw" type="text/javascript"></script>
   <script type="text/javascript">

   function initialize() {
   if (GBrowserIsCompatible()) {
     var map = new GMap2(document.getElementById("map_canvas"));

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
                $i++;
            }
        }
    }

     $meanLat = array_sum($allLats) / sizeof($allLats);
     $meanLng = array_sum($allLngs) / sizeof($allLngs);

     $varLat = 0;
     $varLng = 0;

     for ($i = 0; $i < sizeof($allLats); $i++) {
       $varLat += pow($allLats[$i]-$meanLat, 2);
       $varLng += pow($allLngs[$i]-$meanLng, 2);
     }

     $varLat /= sizeof($allLats);
     $varLng /= sizeof($allLngs);

     ?>
     map.setCenter(new GLatLng(<? echo $meanLat.",".$meanLng; ?>), 11);
     <?

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
	 polygon = new GPolygon([
				 new GLatLng(<? echo $lowerLat[$i].",".$lowerLng[$j]; ?>),
				 new GLatLng(<? echo $lowerLat[$i].",".$upperLng[$j]; ?>),
				 new GLatLng(<? echo $upperLat[$i].",".$upperLng[$j]; ?>),
				 new GLatLng(<? echo $upperLat[$i].",".$lowerLng[$j]; ?>),
				 new GLatLng(<? echo $lowerLat[$i].",".$lowerLng[$j]; ?>)],
                                "#f33f00", 5, 0, <? echo '"'.$color.'", '.$opacity; ?>);
	 map.addOverlay(polygon);
	 <?  
       }
     }

   ?>


   map.setUIToDefault();
 }
   }

</script>
</head>
<body onload="initialize()" onunload="GUnload()">
    <div  id="map_canvas" style="width: 800px; height: 600px"></div>
    <div>
        <form name="input" action="GMaps.php" method="get">
        Resolution: <input type="text" name="Resolution" value="<? echo $N; ?>"/>
        Opacity: <input type="text" name="Opacity" value="<? echo $opacity; ?>" />
        <input type="submit" value="Submit" />
        </form>
    </div>

</body>
</html>
