<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>The Burnt City Map</title>
<link rel="stylesheet" href="leaflet.css" />
<script src="leaflet.js"></script>
<script src="jquery-3.6.1.min.js"></script>

<style>
html, body {
    height: 100%;
    margin: 0;
}
</style>

</head>
<body>

<div id="map" style="width: 100%; height: 100%;"></div>
<script>

<?php

//
// this sets up the different colours of marker icons
//
$marker_icons = array('blackIcon'  => 'markers/marker-icon-2x-black.png',
                      'greyIcon'   => 'markers/marker-icon-2x-grey.png',
		      'orangeIcon' => 'markers/marker-icon-2x-orange.png',
		      'goldIcon'   => 'markers/marker-icon-2x-gold.png',
		      'blueIcon'   => 'markers/marker-icon-2x-blue.png',
		      'redIcon'    => 'markers/marker-icon-2x-red.png',
		      'greenIcon'  => 'markers/marker-icon-2x-green.png',
                      'violetIcon' => 'markers/marker-icon-2x-violet.png');

foreach ($marker_icons as $iconname => $iconfile) {
	print ('var '.$iconname.'= new L.Icon({');
	print ('iconUrl: \''.$iconfile.'\',');
	print ('iconSize: [25, 41],');
        print ('iconAnchor: [12, 41],');
        print ('popupAnchor: [1, -34],');
        print ('shadowSize: [41, 41],');
        print ('className: \'text-labels\'');
	print ('});');
        print (PHP_EOL);
}

print (PHP_EOL);

//
// define the different marker layers we want to use, and set some
// attributes for them all like the marker colour, what the Google
// Sheet ID is where the marker data lives etc. All good stuff.
//

$marker_layers=[
    ['id' => 1,
    'name' => 'NAME_YOU_WANT_FOR_THIS_LAYER',
    'display_name' => 'Objects',
    'iconcolour' => 'blueIcon',
    'sheetid' => 'GOOGLE_SHEET_ID'],
];

// create the layer 'group' that we'll put our markers in
foreach ($marker_layers as ["name" => $layername]) {
    print ('const '.$layername.' = L.layerGroup();');
    print (PHP_EOL);
}

print (PHP_EOL);

// this loads the PHP Google API module we use
include 'vendor/autoload.php';

// this is a substitution array to get rid of characters form
// the Google Sheet cells that we need to replace with HTML
// entities otherwise it causes code errors and the page will
// not render correctly or break completely
$quotes = array(
    "\xC2\xAB"     => '"',
    "\xC2\xBB"     => '"', // » (U+00BB) in UTF-8
    "\xE2\x80\x98" => "&apos;", // ‘ (U+2018) in UTF-8
    "\xE2\x80\x99" => "&apos;", // ’ (U+2019) in UTF-8
    "\xE2\x80\x9A" => "&apos;", // ‚ (U+201A) in UTF-8
    "\xE2\x80\x9B" => "&apos;", // ‛ (U+201B) in UTF-8
    "\xE2\x80\x9C" => '&quot;', // “ (U+201C) in UTF-8
    "\xE2\x80\x9D" => '&quot;', // ” (U+201D) in UTF-8
    "\xE2\x80\x9E" => '&quot;', // „ (U+201E) in UTF-8
    "\xE2\x80\x9F" => '&quot;', // ‟ (U+201F) in UTF-8
    "\xE2\x80\xB9" => "'", // ‹ (U+2039) in UTF-8
    "\xE2\x80\xBA" => "'", // › (U+203A) in UTF-8
);

//
// we need these definitions to setup the Google Docs API
//
$client = new \Google_Client();
$client->setApplicationName('The Burnt City Map');
$client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');

// this is quite an important file, we store it outside the webroot
// so that it can't easily be viewed by a direct HTTP GET
$client->setAuthConfig('/var/www/etc/credentials.json');

//
// this absolute monster of a loop gathers all the markers from 
// the sheets we have defined above and creates the marker objects
// for each single marker. It'll get unwieldy, but this is the future,
// right? we should be able to cope.
//
foreach ($marker_layers as ["name" => $layername,"sheetid" => $sheetid, "iconcolour" => $iconcolour]) {
    $sheets = new \Google_Service_Sheets($client);
    $data = [];
    $currentRow = 2;

    $range = 'A2:G';
    $rows = $sheets->spreadsheets_values->get($sheetid, $range, ['majorDimension' => 'ROWS']);
    if (isset($rows['values'])) {
	    foreach ($rows['values'] as $row) {
            // if any of cells 0-4 are empty we abort
            if (empty($row[0]) || empty($row[1]) || 
		empty($row[2]) || empty($row[3]) ||
		empty($row[4])) {
                break;
            }
            if (empty($row[5])) {
                $row[5]='';
            }
            if (empty($row[6])) {
                $row[6]='';
            }

            $data[] = [
                'col-a' => $row[0],
                'col-b' => $row[1],
                'col-c' => $row[2],
                'col-d' => $row[3],
                'col-e' => $row[4],
                'col-f' => $row[5],
                'col-g' => $row[6]
            ];
        }
    }

    $keys = array_keys($data);
    for($i = 0; $i < count($data); $i++) {
        if ($data[$i]['col-b'] != '') {
	    $name=$layername.$i;
	    $author=$data[$i]['col-b'];
	    $dateadded=$data[$i]['col-a'];

	    $text='<b>';
	    $text=$text.$data[$i]['col-e'].'</font></b><br /><br />';
	    if ($data[$i]['col-f'] != '') {
		$text=$text.$data[$i]['col-f'].'<br /><br />';
            }
	    if ($data[$i]['col-g'] != '') {
		$text=$text.$data[$i]['col-g'].'<br /><br />';
            }

	    $text=$text."Added by: <b>".$author;
	    $text=$text."</b> on <b>".$dateadded."</b><br />";

	    $remove=array("'");
	    $text=str_replace($remove, '&apos;', $text);
	    $remove = array("\n", "\r\n", "\r", "<p>", "</p>", "<h1>", "</h1>");
	    $text=nl2br($text);
	    $text= str_replace($remove, ' ', $text);
	    $text = strtr($text, $quotes);
            $y=$data[$i]['col-c'];
            $x=$data[$i]['col-d'];
            print ('const '.$name.'=L.marker(['.$y.','.$x.'], {icon: '.$iconcolour.'}).bindPopup(\''.$text.'\').addTo('.$layername.');');
            print (PHP_EOL);
	}
    }
    print (PHP_EOL);
}

?>

// define our map object and how much we can zoom it
const map = L.map('map', {
	crs: L.CRS.Simple,
	minZoom: -2,
	maxZoom: 4
});

// define the svg layer which has the actual map
const bounds = [[-26.5, -25], [1021.5, 1023]];
const image = L.imageOverlay('tbc.svg', bounds, {
    attribution: '<a href="index.html">See here for Credits</a>'
}).addTo(map);

map.setView([500, 500], 0.1);

// the key which denotes what the colours mean
var mapkey = L.imageOverlay('mapkey.svg', bounds, {
    maxZoom: 19
});

// text labels on the map
var textlayer = L.imageOverlay('textlabels.svg', bounds, {
    maxZoom: 19
});


// define our overlays
const overlays = {
'Key': mapkey,
'Text': textlayer,

<?php

foreach ($marker_layers as ["name" => $layername, "display_name" => $display_name]) {

    print ('\''.$display_name.'\': '.$layername.',');
    print (PHP_EOL);
}

?>

};

// add the layers we have defined to the map
const layerControl = L.control.layers({}, overlays).addTo(map);

// when we first load the page we expand the layer palette
layerControl.expand();

// add the map key layer immediately on load, but people can turn it off
map.addLayer(mapkey);

// this is an accessibility hack to enable larger fonts 
// when you zoom in a certain amount for people with vision issues
// otherwise the popup text is never enlarged
map.on('zoomstart', function () {
    var zoomLevel = map.getZoom();
    var tooltip = $('.leaflet-popup-content');
	    
    if (zoomLevel < 3) {
            tooltip.css('font-size', 14);
    } else {
            tooltip.css('font-size', 38);
    }
});

</script>

</body>
</html>



