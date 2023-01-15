# **Install instructions**


## **Prerequisites**

You'll need the following things setup and working already.

- A PHP enabled web server

The following prerequisites I'll talk you through in a moment.

- LeafletJS
- Google Docs API PHP module
- JQUERY
- Pointhi's Colour marker set (or similar)

## **Lets do this thing**

So lets assume you are installing into /var/www/html/map, I'm going to reference this as $WEBROOT from now on.

Strap in, this is going to be bumpy.

### Install some map markers

You want to have a $WEBROOT/markers directory and to install some marker images into that directory.

If you are stuck, use pointhi's from their github and place them in that markers directory. You can use any marker set you want which chimes with whatever license you want to use. These will get you started.

```
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-black.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-blue.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-gold.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-green.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-grey.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-orange.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-red.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-violet.png
https://github.com/pointhi/leaflet-color-markers/blob/master/img/marker-icon-yellow.png
```

### Download Leafletjs

I used 1.9.3

https://leafletjs-cdn.s3.amazonaws.com/content/leaflet/v1.9.3/leaflet.zip

Extract the files from that .zip and move then into $WEBROOT so that $WEBROOT has the .js, .css, .map files etc.

```
leaflet-src.js
leaflet-src.js.map
leaflet.css
leaflet.js
leaflet.js.map
leaflet-src.esm.js
leaflet-src.esm.js.map
```

### Install the Google APIclient

I used composer to do this for me but if you know how to do this manually that'll work.

> composer install google/apiclient

### Download JQUERY

I used [this one](https://code.jquery.com/jquery-3.6.3.min.js) but honestly use whatever the latest version is.

Again, copy it into your $WEBROOT.

### Download SVG layers

The SVG layers are basically layered ontop of each other. You can enforce the displaying of the layers in the map.php file or you can use the layer palette to turn then on and off as you wish.

The layers are :

mapkey.svg - this one is the key for the map which identifies what each colour is etc.
tbc.svg - this is the actual map itself
textlabels.svg - this is the text labels that define what each area is on the map

Download and install all of these into your $WEBROOT

### Setup API access for your Google account

If you want to access your Google sheet via a web API you have to have API access enabled. This will enable you to access your Google sheet using a Service Account. This process looks horribly complicated, but actually if you just follow a good guide you'll end up with a credentials.json file and everything will be fine.

[This guide is pretty good](https://blog.golayer.io/google-sheets/google-sheets-api)

### Install credentials.json somewhere safe

Your Google API credentials ideally need to be stored outside of $WEBROOT for the simple reason, they're credentials and you don't want them ending up in the wrong hands. If you look at the source of map.php there's a line in there that states :

> $client->setAuthConfig('/var/www/etc/credentials.json');

This is where it expects to find this credentials file. If you store it somewhere else, that's cool, just change this line to reflect that.

### Create a Google Sheet that will hold your map markers

Row 1 should be your Column headers. I use the following headers in Columns A, B, C, D, E, F

```
Date 
Added By
Y Co-Ordinates
X Co-Ordinates
Title
Description
```

Row 2 onwards is for your actual data. So for example.

```
A 04/12/2022 20:26
B d834256
C 580
D 855
E Painting in Hades Office
F Behind Hades desk there's a painting. It's called "Fall of the damned" and it's by Dieric Bouts.
```

### Share your Google Sheet with your service account user

When you created your Google Service Account in the step above it created a service account user for you. Simply share your Google Sheet with this user. map.php should then be able to read the data from it.

### Install index.php and tweak

Once you have your Google API service account setup, and your Google Sheet created you want to setup map.php.

Look for this array.

```
 $marker_layers=[
         ['id' => 1,
          'name' => 'NAME_YOU_WANT_FOR_THIS_LAYER',
          'display_name' => 'Objects',
          'iconcolour' => 'blueIcon',
          'sheetid' => 'GOOGLE_SHEET_ID'],
 ];
 ```

For each layer you add, increment the id number.

The iconcolour element refers to an array in map.php, you want to put the name here of the map marker icon in this array.

``` 
$marker_icons = array('blackIcon'  => 'markers/marker-icon-2x-black.png',
                       'greyIcon'   => 'markers/marker-icon-2x-grey.png',
                       'orangeIcon' => 'markers/marker-icon-2x-orange.png',
                       'goldIcon'   => 'markers/marker-icon-2x-gold.png',
                       'blueIcon'   => 'markers/marker-icon-2x-blue.png',
                       'redIcon'    => 'markers/marker-icon-2x-red.png',
                       'greenIcon'  => 'markers/marker-icon-2x-green.png',
                       'violetIcon' => 'markers/marker-icon-2x-violet.png');
```


For the sheetid, this is contained in the URL for your Google Sheet. If you look in the address bar in your browser, it's in this format :

docs.google.com/spreadsheets/d/**__THIS_IS_YOUR_SHEET_ID__**/edit#gid=0

### Adding marker layers

Basically you edit the array above and just add another element to it. Usually making your map markers a different colour for each layer is a good idea. You can give people edit permission to specific sheets depending on your use case.
