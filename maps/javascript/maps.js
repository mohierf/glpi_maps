// -- coding: utf-8 --
// Google API not yet loaded ...
var apiLoaded=false;
var apiLoading=false;
// Set true to activate javascript console logs
var debugJs=false;

var map;
var infoWindow;

// France / Romans is default camera position
var defLat=45.31698;
var defLng=5.45124;
var defaultZoom=16;
var currentZoom=defaultZoom;

// Images and scripts dir
var imagesDir="./";
var scriptsDir="./";

// Markers ...
var allMarkers = [];
// Content of infoWindow ...
var infoWindowsArray = [];

// Couleur de fond selon l'état
var customBackground = {
	ND: { couleur: '#9999ff' },
	ES: { couleur: '#a3feba' },
	HS: { couleur: '#daa520' },
	SR: { couleur: '#ffa8a8' },
	IS: { couleur: 'steelblue' },
	MP: { couleur: 'powderblue' },
	FT: { couleur: 'powderblue' },
	TU: { couleur: 'powderblue' }
};

var deselectCurrent = function() {};

//------------------------------------------------------------------------------
// Google maps API loading if needed, and map creation ...
//------------------------------------------------------------------------------
// If google maps API is not already loaded, call this function which will, at
// end, call mapInit ...
//------------------------------------------------------------------------------
function mapLoad() {
	if (debugJs) console.log('mapLoad');

	if (! apiLoaded) {
		apiLoading=true;
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = "http://maps.googleapis.com/maps/api/js?sensor=false&callback=mapInit";
		document.body.appendChild(script);
	} else {
		mapInit();
	}
}

// marker initialization
// point : GPS coordinates
// name : host name
// state : host state
// content : infoWindow content
//------------------------------------------------------------------------------
markerCreate = function(point, name, state, content, iconBase) {
	// if (debugJs) console.log("markerCreate for "+name+", state : "+state);

	var iconUrl=imagesDir+'/'+iconBase+"-"+state+".png";
	if (state == '') iconUrl=imagesDir+'/'+iconBase+".png";
	// if (debugJs) console.log("markerCreate, icon URL : "+iconUrl);
	
	var image = new google.maps.MarkerImage(iconUrl, new google.maps.Size(32,32), new google.maps.Point(0,0), new google.maps.Point(16,32));

	try {
	/* Standard Google maps marker
		var marker = new google.maps.Marker({
			map: map, position: point, raiseOnDrag: false,
			icon: image, 
			animation: google.maps.Animation.DROP,
			title: host.name
		});
	*/
		// Marker with label ...
		var marker = new MarkerWithLabel({
			map: map, position: point,
			icon: image, 
			raiseOnDrag: false, draggable: true,
			title: name,
			hoststate: state,
			hostname: name,
			iw_content: content,

			// Half the CSS width to get a centered label ...
			labelAnchor: new google.maps.Point(50, 40),
			labelClass: "labels",
			labelContent: name,
			labelStyle: {opacity: 0.50}
		});
		// Register Custom "dragend" Event
		google.maps.event.addListener(marker, 'dragend', function() {
			// Get the Current position, where the pointer was dropped
			var point = marker.getPosition();
			// Center the map at given point
			map.panTo(point);
			// Update the textbox (if needed ...)
			// document.getElementById('txt_latlng').value=point.lat()+", "+point.lng();
		});
	
	} catch (e) {
		if (debugJs) console.error('markerCreate, exception : '+e.message);
	}
	
	return marker;
}

// Map initialization
// Global hostsInfo needs to be defined before calling this function
// This function is a callback called after Google maps API is fully loaded
mapInit = function() {
	if (debugJs) console.log('mapInit ...');
	if (apiLoading) {
		apiLoaded=true;
	}
	if (! apiLoaded) {
		console.error('Google Maps API not loaded. Call mapLoad function ...');
		return false;
	}
	
	if (hostsInfo == undefined) {
		console.error('Hosts information are not available. Set hostsInfo array content before calling this function ...');
		return false;
	}
	// "Spiederify" close markers : https://github.com/jawj/OverlappingMarkerSpiderfier
	Ext.Loader.load([ scriptsDir+'/oms.min.js' ], function() {
		if (debugJs) console.log('Spiderify API loaded ...');
		Ext.Loader.load([ debugJs ? scriptsDir+'/markerclusterer.js' : scriptsDir+'/markerclusterer_packed.js' ], function() {
			if (debugJs) console.log('Google marker clusterer API loaded ...');
			Ext.Loader.load([ debugJs ? scriptsDir+'/markerwithlabel.js' : scriptsDir+'/markerwithlabel_packed.js' ], function() {
				if (debugJs) console.log('Google labeled marker API loaded ...');
				
				map = new google.maps.Map(document.getElementById('map'),{
					center: new google.maps.LatLng (defLat, defLng),
					zoom: defaultZoom,
					mapTypeId: google.maps.MapTypeId.ROADMAP
				});

				infoWindow = new google.maps.InfoWindow;
				
				var bounds = new google.maps.LatLngBounds();
				for (var index = 0; index < hostsInfo.length; ++index) {
					var host = hostsInfo[index];
					var hostState = host.state ? host.state.toUpperCase() : '';
					var hostGlobalState = -1;
					var gpsLocation = new google.maps.LatLng(host.lat, host.lng);
					var iconBase='host';
					// if (debugJs) console.log('host '+host.name+' is '+hostState+', located here : '+gpsLocation);
					if (hostState != 'UP') console.error('host '+host.name+' is '+hostState+', located here : '+gpsLocation);

					if (host.monitoring && host.monitoring == true) {
						iconBase='host-monitored';
						
						switch(hostState) {
							case "UP":
								hostGlobalState=0;
								break;
							case "DOWN":
								hostGlobalState=2;
								break;
							default:
								hostGlobalState=1;
								break;
						}
						// if (debugJs) console.log('-> host global state : '+hostGlobalState);
						
						var infoViewContent = 
							'<div class="map-infoView" id="iw-'+host.name+'">'+
							'<img class="map-iconstate" src="'+imagesDir+'/Kiosk-'+hostState+'.png" />'+
							'<span class="map-hostname">'+'<a href="'+host.link+'">'+host.name+'</a>'+' is '+host.state+'.</span>'+
							'<hr/>';
						if (host.services != undefined) {
							infoViewContent += '<ul class="map-servicesList">';
							for (var idxServices = 0; idxServices < host.services.length; ++idxServices) {
								var service = host.services[idxServices];
								var serviceState = service.state.toUpperCase();
								// if (debugJs) console.log(' - service '+service.name+' is '+serviceState);
								if (serviceState != 'OK') console.error(' - service '+service.name+' is '+serviceState);
								infoViewContent += '<li><span class="map-service map-service-'+serviceState+'">&nbsp;</span>'+service.name+' is '+serviceState+'.</li>';
								
								switch(serviceState) {
									case "OK":
										break;
									case "UNKNOWN":
									case "PENDING":
									case "WARNING":
										if (hostGlobalState < 1) hostGlobalState=1;
										break;
									case "CRITICAL":
										if (hostGlobalState < 2) hostGlobalState=2;
										break;
								}
								// if (debugJs) console.log('-> host global state : '+hostGlobalState);
							}
							infoViewContent += '</ul>';
						}
						infoViewContent += '</div>';
					} else {
						var infoViewContent = 
							'<div class="map-infoView" id="iw-'+host.name+'">'+
							'<span class="map-hostname">'+'<a href="'+host.link+'">'+host.name+'</a>'+
							'</span>'+
							'</div>';
					}
					
					// Create a marker ...
					var markerState = "UNKNOWN";
					switch(hostGlobalState) {
						case -1:
							markerState = "";
							break;
						case 0:
							markerState = "OK";
							break;
						case 2:
							markerState = "KO";
							break;
						default:
							markerState = "WARNING";
							break;
					}
					allMarkers.push(markerCreate(gpsLocation, host.name, markerState, infoViewContent, iconBase));
					bounds.extend(gpsLocation);
				}
				map.fitBounds(bounds);
				
				var mcOptions = {
					zoomOnClick: true, showText: true, averageCenter: true, gridSize: 10, minimumClusterSize: 2, maxZoom: 14,
					styles: [
						{ height: 50, width: 50, url: imagesDir+"/cluster-OK.png" },
						{ height: 60, width: 60, url: imagesDir+"/cluster-WARNING.png" },
						{ height: 60, width: 60, url: imagesDir+"/cluster-KO.png" }
					]
					,
					calculator: function(markers, numStyles) {
						// Manage markers in the cluster ...
						if (debugJs) console.log("marker, count : "+markers.length);
						if (debugJs) console.log(markers);
						var clusterIndex = 1;
						for (i=0; i < markers.length; i++) {
							var currentMarker = markers[i];
							if (debugJs) console.log("marker, "+currentMarker.hostname+" state is : "+currentMarker.hoststate);
							// if (debugJs) console.log(currentMarker);
							switch(currentMarker.hoststate.toUpperCase()) {
								case "OK":
									break;
								case "WARNING":
									if (clusterIndex < 2) clusterIndex=2;
									break;
								case "KO":
									if (clusterIndex < 3) clusterIndex=3;
									break;
							}
						}

						if (debugJs) console.log("marker, index : "+clusterIndex);
						return {text: markers.length, index: clusterIndex};
					}
				};
				var markerCluster = new MarkerClusterer(map, allMarkers, mcOptions);

				var usualColor = 'eebb22';
				var spiderfiedColor = 'ffee22';
				var iconWithColor = function(color) {
					return 'http://chart.googleapis.com/chart?chst=d_map_xpin_letter&chld=pin|+|' + color + '|000000|ffff00';
				}
				var shadow = new google.maps.MarkerImage(
					'https://www.google.com/intl/en_ALL/mapfiles/shadow50.png',
					new google.maps.Size(37, 34),  // size   - for sprite clipping
					new google.maps.Point(0, 0),   // origin - ditto
					new google.maps.Point(10, 34)  // anchor - where to meet map location
				);
				var oms = new OverlappingMarkerSpiderfier(map, {markersWontMove: true, markersWontHide: true});
				console.log(oms);
				oms.addListener('click', function(marker) {
					if (debugJs) console.log('click ...');
					infoWindow.setContent(marker.iw_content);
					infoWindow.open(map, marker);
				});
				oms.addListener('spiderfy', function(markers) {
					if (debugJs) console.log('spiderfy ...');
					// for(var i = 0; i < markers.length; i ++) {
						// markers[i].setIcon(iconWithColor(spiderfiedColor));
						// markers[i].setShadow(null);
					// } 
					infoWindow.close();
				});
				oms.addListener('unspiderfy', function(markers) {
					if (debugJs) console.log('unspiderfy ...');
					// for(var i = 0; i < markers.length; i ++) {
						// markers[i].setIcon(iconWithColor(usualColor));
						// markers[i].setShadow(shadow);
					// }
				});
				
				for (i=0; i < allMarkers.length; i++) {
					oms.addMarker(allMarkers[i]);
				}
			});
		});
	});

	return true;
};

function getRandomPoint() {
  var lat = defLat + (Math.random() - 0.5) * 5.5;
  var lng = defLng + (Math.random() - 0.5) * 10.0;
  return new google.maps.LatLng(Math.round(lat * 10) / 10, Math.round(lng * 10) / 10);
}

// ...
function boutonClique() {
	if (debugJs) console.log('boutonClique');
	alert("Faire quelque chose !");
	selectionMap = true;
}

// Drop d'un élément dans la carte
function dropCarte(event) {
	if (debugJs) console.warn("dropCarte, event : "+event.type);

	// Ne rien faire ...
	alert("Fonctionnalité non disponible !");
	return;

	if (event.dataTransfer) {
		if (debugJs) console.log("dropCarte, event.dataTransfer ...");
		var format = "Text";
		var textData = event.dataTransfer.getData (format);
		if (! textData) {
			textData = "<span style='color:red'>The data transfer contains no text data.</span>";
		}

		var targetDiv = document.getElementById ("target");
		alert(textData);
    }
	
	// La position absolue de la carte sur l'écran ...
	var mapOffset = $('carteMap').cumulativeOffset();
	mLeft = mapOffset.left;
	mTop = mapOffset.top;
	var mapDimensions = $('carteMap').getDimensions();
	mWidth = mapDimensions.width;
	mHeight = mapDimensions.height;

	// La position où a été laché l'objet ...
	var x = event.pointerX();
	var y = event.pointerY();

	if (debugJs) console.log("dropCarte, x="+x+", y="+y);
	if (debugJs) console.log("dropCarte, mLeft="+mLeft+", mTop="+mTop);
	if (debugJs) console.log("dropCarte, mWidth="+mWidth+", mHeight="+mHeight);
	
	// Check if the cursor is inside the map div
	if (x > mLeft && x < (mLeft + mWidth) && y > mTop && y < (mTop + mHeight)) {
		if (debugJs) console.log("dropCarte, cursor in map ...");
		// Difference between the x property of iconAnchor
		// and the middle of the icon width
		var anchorDiff = 1;

		// Find the object's pixel position in the map container
		var pixelpoint = new google.maps.Point(x - mLeft -anchorDiff, y - mTop);

		// Corresponding geo point on the map
		var overlayProjection = dummy.getProjection();
		var latlng = overlayProjection.fromContainerPixelToLatLng(pixelpoint);

		// Create a corresponding marker on the map
		var html='<strong>Nouveau point</strong><br/><button type="button" onClick="boutonClique();">Cliquer !</button>';
		createMarker(latlng, "Nouveau site", html, marqueOrange);
		//$('boutonSite').observe('click', function(event) { alert('Bouton cliqué !'); });
	} else {
		if (debugJs) console.log("dropCarte, cursor not in map !");
	}
};

// Fonction pour créer un marqueur ...
var selectedMarker=null;
function createMarker(latlng, name, html, src) {
	if (debugJs) console.log('createMarker: html='+html);
	
    var marker = new google.maps.Marker({
        position: latlng,
        map: map,
		draggable: true,
		title: name,
        zIndex: Math.round(latlng.lat()*-100000)<<5
	});
	if (! marker) return;
	
    google.maps.event.addListener(marker, 'click', function() {
		if (debugJs) console.log('clickMarker: ');
		selectedMarker=marker;
        infoWindow.setContent(html); 
		geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					infoWindow.setContent(infoWindow.getContent()+"<br/>"+results[0].formatted_address);
				}
			}
		});
        infoWindow.open(map,marker);
	});

	google.maps.event.addListener(marker, "dragstart", function(event) {
		// Close infowindow when dragging the marker whose infowindow is open
		if (selectedMarker == marker) infoWindow.close();
		infoWindow.setContent(infoWindow.getContent()+"<br/><b>Ancienne position</b> : "+event.latLng);
	});
	
	google.maps.event.addListener(marker, "dragend", function(event) {
		// Close infowindow when dragging the marker whose infowindow is open
		if (selectedMarker == marker) infoWindow.open(map, marker);
		
		geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					infoWindow.setContent(infoWindow.getContent()+"<br/>"+results[0].formatted_address);
				}
			}
		});
	});

	selectedMarker=marker;
    google.maps.event.trigger(marker, 'click');

    return marker;
}
