<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Google Maps JavaScript API Example</title>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=ABQIAAAA1i3A2AVMsUWXBX44vDLeLBRbHvJD7lrrzhX73ZhbfnOuHeGXeRRZOl_Zoq2b4PtNHMy0W3iqC-UF1Q" type="text/javascript"></script>
    <script type="text/javascript">

    function initialize() {
      if (GBrowserIsCompatible()) {
        var map = new GMap2(document.getElementById("map_canvas"));

<? 

        $array = file("coords.dat");

        for ($i = 0; $i < sizeof($array)/10; $i++) {
            $newArr[$i] = split(" ", $array[$i]);
            $allLats[$i] = $newArr[$i][0];
            $allLngs[$i] = str_replace("\n", "", $newArr[$i][1]);
        }

        $Nx = 25;
	$Ny = 25;
        for ($i = 0; $i < $Nx; $i++) {
            for ($j = 0; $j < $Ny; $j++) {
                $freqArr[$i][$j] = 0;
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
        map.setCenter(new GLatLng(<? echo $meanLat.",".$meanLng; ?>), 13);
<?

   $minLng = $meanLng - 20*$varLng;
   $maxLng = $meanLng + 20*$varLng;
   $maxLat = $meanLat + 20*$varLat;
   $minLat = $meanLat - 20*$varLat;
   /*
   $minLat = min($allLats);
   $minLng = min($allLngs);
 
   $maxLat = max($allLats);
   $maxLng = max($allLngs);
   */
   $lngSpan = $maxLng - $minLng;
   $latSpan = $maxLat - $minLat;
   $binLngSpan = $lngSpan / $Ny;
   $binLatSpan = $latSpan / $Nx;
?>

        var polygon;
        polygon = new GPolygon([new GLatLng(<? echo $minLat.",".$minLng; ?>),
                                new GLatLng(<? echo $minLat.",".$maxLng; ?>),
                                new GLatLng(<? echo $maxLat.",".$maxLng; ?>),
                                new GLatLng(<? echo $maxLat.",".$minLng; ?>),
                                new GLatLng(<? echo $minLat.",".$minLng; ?>)],
                                "#f33f00", 5, 0, "#ff0000", 0.10);
        map.addOverlay(polygon);


<? 
        for ($i = 0; $i < sizeof($allLats); $i++) {
            $cntLat[$i] = 0;
            $cntLng[$i] = 0;
        }


        $offset = 0.001;
        for ($i = 0; $i < sizeof($allLats); $i++) {
	  /*
            $x  = (float)$allLats[$i] - $offset/2;
            $xp = (float)$allLats[$i] + $offset/2;
            $y  = (float)$allLngs[$i] - $offset/2;
            $yp = (float)$allLngs[$i] + $offset/2;
	  */
			  $ybin = intval(($allLngs[$i] - $minLng) / $lngSpan * $Ny);
			  $xbin = intval(($allLats[$i] - $minLat) / $latSpan * $Nx);
			  if($xbin < 0 or $xbin > $Nx - 1)
				     continue;
			if($ybin < 0 or $ybin > $Ny - 1)
				   continue;
			  $y = (float)$ybin * $binLngSpan + $minLng;
			  $x = (float)$xbin * $binLatSpan + $minLat;
			  $yp = $y + $binLngSpan;
			  $xp = $x + $binLatSpan;

?>
            polygon = new GPolygon([
                            new GLatLng(<? echo $x.",".$y; ?>),
                            new GLatLng(<? echo $x.",".$yp; ?>),
                            new GLatLng(<? echo $xp.",".$yp; ?>),
                            new GLatLng(<? echo $xp.",".$y; ?>),
                            new GLatLng(<? echo $x.",".$y; ?>)],
                            "#f33f00", 5, 0, "#000000", <? echo $ybin/($Ny-1) ?>);
            map.addOverlay(polygon);
<?
        }

?>


        map.setUIToDefault();
      }
    }

    </script>
  </head>
  <body onload="initialize()" onunload="GUnload()">
    <div id="map_canvas" style="width: 95%; height: 95%"></div>

<? echo $minLng." ".$maxLng."<br>"; ?>
<? echo $minLat." ".$maxLat."<br>"; ?>
<? echo $varLat." ".$varLng."<br>"; ?>
<? echo "lngSpan: $lngSpan <br>"?>
<? echo "latSpan: $latSpan <br>"?>
<? echo "binLngSpan: $binLngSpan <br>"?>
<? echo "binLatSpan: $binLatSpan <br>"?>


  </body>
</html>
