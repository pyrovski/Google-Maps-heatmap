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

        var xc;
        var yc;
        xc = 32.221667;
        yc = -110.926389;

        var offset;
        offset = 0.01

        map.setCenter(new GLatLng(xc, yc), 13);

<? 
        $array = file("coords.dat");

        for ($i = 0; $i < sizeof($array); $i++) {
            $newArr[$i] = split(" ", $array[$i]);
            $allLats[$i] = $newArr[$i][0];
            $allLngs[$i] = str_replace("\n", "", $newArr[$i][1]);
        }
        $minLat = min($allLats);
        $minLng = min($allLngs);

        $maxLat = max($allLats);
        $maxLng = max($allLngs);
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
            $x  = (float)$allLats[$i];
            $xp = (float)$allLats[$i] + $offset;
            $y  = (float)$allLngs[$i];
            $yp = (float)$allLngs[$i] + $offset;


?>
            polygon = new GPolygon([
                            new GLatLng(<? echo $x.",".$y; ?>),
                            new GLatLng(<? echo $x.",".$yp; ?>),
                            new GLatLng(<? echo $xp.",".$yp; ?>),
                            new GLatLng(<? echo $xp.",".$y; ?>),
                            new GLatLng(<? echo $x.",".$y; ?>)],
                            "#f33f00", 5, 0, "#000000", 1.00);
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


  </body>
</html>
