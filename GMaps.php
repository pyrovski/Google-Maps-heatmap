<? require_once("include/functions.inc"); ?>
<!DOCTYPE html "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
   <head>
   <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
   <title>Google Maps JavaScript API Example</title>

<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript">

Array.prototype.max = function() {
    var max = this[0];
    var len = this.length;
    for (var i = 1; i < len; i++) if (this[i] > max) max = this[i];
    return max;
}

Array.prototype.min = function() {
    var min = this[0];
    var len = this.length;
    for (var i = 1; i < len; i++) if (this[i] < min) min = this[i];
    return min;
}

var myPolygons;
var allLats;
var allLngs;
var allDates;
var meanLat;
var meanLng;
var centerLat;
var centerLng;
var varLat;
var varLng;
var minLat;
var minLng;
var maxLat;
var maxLng;
var myMap;
var opacity;
var N;
var overlayNum;
var numEvents;

function retrieveRows() {
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
                $allLats[$i]  = $row['Latitude'];
                $allLngs[$i]  = $row['Longitude'];
                $allDates[$i] = $row['EventDate']; 
                $i++;
            }
        }
        $numEvents = $i;

?>
        allLats = new Array(
<?
            $i = 0;
            while ($i < $numEvents) {
                echo  "$allLats[$i]";
                if ($i < $numEvents-1) {
                    echo  ",";
                } 
                $i++;
            }
            ?>);

        allLngs = new Array(
<?
            $i = 0;
            while ($i < $numEvents) {
                echo  "$allLngs[$i]";
                if ($i < $numEvents-1) {
                    echo  ",";
                } 
                $i++;
            }
            ?>);

        allDates = new Array(
<?
            $i = 0;
            while ($i < $numEvents) {
                echo  "$allDates[$i]";
                if ($i < $numEvents-1) {
                    echo  ",";
                } 
                $i++;
            }
            ?>);
<?
    }
?>
    numEvents = <? echo $numEvents; ?>;
}


function drawPolygons(N, newOpacity) {
    var lowerLat = [];
    var lowerLng = [];
    var upperLat = [];
    var upperLng = [];
    var lngSpan;
    var latSpan;
    var binLngSpan;
    var binLatSpan;


    var freqArr = new Array(N);

    for (i = 0; i < N; i++) {
        freqArr[i] = new Array(N);
        for (j = 0; j < N; j++) {
            freqArr[i][j] = 0;
            lowerLat[i] = minLat +     i*(maxLat-minLat)/N;
            upperLat[i] = minLat + (i+1)*(maxLat-minLat)/N;

            lowerLng[i] = minLng +     i*(maxLng-minLng)/N;
            upperLng[i] = minLng + (i+1)*(maxLng-minLng)/N;
        }
    }

    lngSpan = maxLng - minLng;
    latSpan = maxLat - minLat;
    binLngSpan = lngSpan / N;
    binLatSpan = latSpan / N;

    var ybin;
    var xbin;

    for (i = 0; i < allLats.length; i++) {
        ybin = parseInt((allLngs[i] - minLng) / lngSpan * N);
        xbin = parseInt((allLats[i] - minLat) / latSpan * N);
        if (xbin < 0 || xbin > (N - 1)) continue;
        if (ybin < 0 || ybin > (N - 1)) continue;
        freqArr[xbin][ybin]++;
    }

    var maxFreq = 0;
    for (i = 0; i < N; i++) {
        if (maxFreq < freqArr[i].max()) {
            maxFreq = freqArr[i].max();
        }
    }

    overlayNum = 0;
    var intensity;
    var colorDecimal;
    var colorHex;
    var colorString;
    myPolygons = new Array(N*N);

    for (i = 0; i < N; i++) {
        for (j = 0; j < N; j++) {
            intensity = freqArr[i][j] / maxFreq;
            if (intensity == 0.000)  { continue;
            } else if (intensity < 0.90) {
                rPart = 255 - intensity*(255-128)
                gPart = 248 - intensity*(248-0)
                bPart = 220 - intensity*(220-0)
                colorString = "#";
                rHex = parseInt(rPart,10).toString(16);
                if (rHex.length < 2) rHex = "0" + rHex;
                colorString += rHex;

                gHex = parseInt(gPart,10).toString(16);
                if (gHex.length < 2) gHex = "0" + gHex;
                colorString += gHex;

                bHex = parseInt(bPart,10).toString(16);
                if (bHex.length < 2) bHex = "0" + bHex;
                colorString += bHex;

            } else {
                colorString  = "#800000";
            }
  <?        if (isset($_GET['Opacity'])) { ?>
                opacity = <? echo $_GET['Opacity']; ?>; <?
            } else { ?>
                opacity = newOpacity; <?
            } ?>
            
            var boxCoords = [new google.maps.LatLng(lowerLat[i], lowerLng[j]),
                             new google.maps.LatLng(lowerLat[i], upperLng[j]),
                             new google.maps.LatLng(upperLat[i], upperLng[j]),
                             new google.maps.LatLng(upperLat[i], lowerLng[j])];

            myPolygons[overlayNum] = new google.maps.Polygon({
                       paths: boxCoords,
                       strokeColor: "#000000",
                       strokeOpacity: 0.0,
                       strokeWeight: 0,
                       fillColor: colorString,
                       fillOpacity: opacity});
            myPolygons[overlayNum].setMap(myMap);
            overlayNum++;
       }
     }
}




function initialize() {
    retrieveRows();

    document.getElementById("numEvents").innerHTML = numEvents;

    var minDate = "<? echo min($allDates); ?>";
    var maxDate = "<? echo max($allDates); ?>";
    var numRows = <? echo $i; ?>;

    document.getElementById("startDate").innerHTML = minDate;
    document.getElementById("endDate").innerHTML   = maxDate;

    meanLat = <? echo array_sum($allLats) / sizeof($allLats); ?>;
    meanLng = <? echo array_sum($allLngs) / sizeof($allLngs); ?>;

    var initZoom;
    var i;
    var j;

<?
    if (isset($_GET['Lat'])) { ?>
        centerLat = <? echo $_GET['Lat']; ?>; <?
    } else { ?>
        centerLat = meanLat; <?
    }
    if (isset($_GET['Lng'])) { ?>
        centerLng = <? echo $_GET['Lng']; ?>; <?
    } else { ?>
        centerLng = meanLng; <?
    }
    if (isset($_GET['Zoom'])) { ?>
        initZoom = <? echo $_GET['Zoom']; ?>; <?
    } else { ?>
        initZoom = 11; <?
    }
?>

    var myLatLng = new google.maps.LatLng(centerLat, centerLng);
    var myOptions = {
                zoom: initZoom,
                center: myLatLng,
                mapTypeId: google.maps.MapTypeId.TERRAIN};
    myMap = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

    var varLat = 0;
    var varLng = 0;

    for (i = 0; i < allLats.length; i++) {
        varLat += Math.pow(allLats[i]-meanLat, 2);
        varLng += Math.pow(allLngs[i]-meanLng, 2);
    }

    varLat /= allLats.length;
    varLng /= allLngs.length;

    minLng = meanLng - 40*varLat;
    maxLng = meanLng + 40*varLat;
    maxLat = meanLat + 40*varLat;
    minLat = meanLat - 40*varLat;

<?
    if (isset($_GET['Resolution'])) { ?>
        N = <? echo $_GET['Resolution']; ?>; <?
    } else { ?>
        N = 25; <?
    } 
?>

    drawPolygons(N, 0.8);
}


function killPolygons() {
    for (i = 0; i < overlayNum; i++) {
        myPolygons[i].setMap(null);
        myPolygons[i] = null;
    }
}

function changeOpacity(newOpacity) {
    for (i = 0; i < overlayNum; i++) {
        myPolygons[i].setOptions({fillOpacity: newOpacity});
    }
}

function changeResolution(newResolution, newOpacity) {
    if (newResolution != N) {
        killPolygons();
        drawPolygons(newResolution, newOpacity);
    }
}

function addAddress() {
    var geocoder = new google.maps.Geocoder();
    var address = document.getElementById("address").value;
    if (geocoder) {
        geocoder.geocode({ 'address': address}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var marker = new google.maps.Marker({
                    map: myMap,
                    position: results[0].geometry.location});
            }
        });
    }
}


</script>
</head>
<body onload="initialize()">
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
                            min="1" max="150" step="1" 
                            value="25" 
                            onChange="changeResolution(
                                this.value,getElementsByName('Opacity')[0].value)" />
        Opacity: <input type="range" name="Opacity" 
                            min="0" max="1.0" step="0.01" 
                            value="0.5" 
                            onChange="changeOpacity(this.value)" />
        </form>
    </div>
  <div style="width: 800px">This map shows police incident density in Tucson,
  showing <div id="numEvents" style="display: inline;"> </div> events over 
  the period beginning <div id="startDate" style="display: inline;"> </div>  
  and ending <div id="endDate" style="display: inline;"> </div>.
  Data is currently sourced from 
  <a href="http://maps.azstarnet.com/crime/show30">the AZ Star website</a>, 
  because the Tucson police department is reluctant to provide us with usable
  data. The source code for this project can be found on
  <a href="https://github.com/pyrovski/Google-Maps-heatmap">Github</a></div>.

<br><br>
</div>
</body>
</html>
