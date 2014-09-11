// -- coding: utf-8 --
// Google API not yet loaded ...
var apiLoaded=false;
var apiLoading=false;
// Set true to activate javascript console logs
var debugJs=false;
if (debugJs && !window.console) {
  alert('Your web browser does not have any console object ... you should stop using IE ;-) !');
}


var map;
var infoWindow;

// France / Romans is default camera position
var defLat=45.31698;
var defLng=5.45124;
var defaultZoom=16;

// Default map layer ...
// Use 'OSM' to use Open Street Map as layer
var mapLayer='OSM';



// Images and scripts dir
var imagesDir="./";
var scriptsDir="./";

// Markers ...
var allMarkers = [];

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
// position : GPS coordinates
// name : host name
// state : host state
// content : infoWindow content
// iconBase : icone name base
//------------------------------------------------------------------------------
markerCreate = function(name, state, content, position, iconBase) {
  if (debugJs) console.log("-> marker creation for "+name+", state : "+state);
  if (iconBase == undefined) iconBase='host';

  var iconUrl=imagesDir+'/'+iconBase+"-"+state+".png";
  if (state == '') iconUrl=imagesDir+'/'+iconBase+".png";

  var markerImage = new google.maps.MarkerImage(
    iconUrl,
    new google.maps.Size(32,32), 
    new google.maps.Point(0,0), 
    new google.maps.Point(16,32)
  );

  try {
    /* Standard Google maps marker */
    var marker = new google.maps.Marker({
      map: map, 
      position: position,
      icon: markerImage, 
      raiseOnDrag: false, draggable: true,
      title: name,
      hoststate: state,
      hostname: name,
      iw_content: content
    });

    /* Marker with label ...
    var marker = new MarkerWithLabel({
      map: map, 
      position: position,
      icon: markerImage, 
      raiseOnDrag: false, draggable: true,
      title: name,
      hoststate: state,
      hostname: name,
      iw_content: content,

      // Half the CSS width to get a centered label ...
      labelAnchor: new google.maps.Point(50, -20),
      labelClass: "labels",
      labelContent: name,
      labelStyle: {opacity: 0.8},
      labelInBackground: true
    });
    */
    
    // Register Custom "dragend" Event
    google.maps.event.addListener(marker, 'dragend', function() {
      // Center the map at given point
      map.panTo(marker.getPosition());
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
    if (debugJs) console.error('Google Maps API not loaded. Call mapLoad function ...');
    return false;
  }

  if (hostsInfo == undefined) {
    if (debugJs) console.error('Hosts information are not available. Set hostsInfo array content before calling this function ...');
    return false;
  }
  // "Spiderify" close markers : https://github.com/jawj/OverlappingMarkerSpiderfier
  Ext.Loader.load([ scriptsDir+'/oms.min.js' ], function() {
    if (debugJs) console.log('Spiderify API loaded ...');
    Ext.Loader.load([ debugJs ? scriptsDir+'/markerclusterer.js' : scriptsDir+'/markerclusterer_packed.js' ], function() {
      if (debugJs) console.log('Google marker clusterer API loaded ...');
      Ext.Loader.load([ debugJs ? scriptsDir+'/markerwithlabel.js' : scriptsDir+'/markerwithlabel_packed.js' ], function() {
        if (debugJs) console.log('Google labeled marker API loaded ...');
        
          if (mapLayer=='OSM') {
            // Define OSM map type pointing at the OpenStreetMap tile server
            map = new google.maps.Map(document.getElementById('map'),{
              center: new google.maps.LatLng (defLat, defLng),
              zoom: defaultZoom,
              mapTypeId: "OSM",
              mapTypeControl: false,
              streetViewControl: false
            });

            map.mapTypes.set("OSM", new google.maps.ImageMapType({
                getTileUrl: function(coord, zoom) {
                    return "http://tile.openstreetmap.org/" + zoom + "/" + coord.x + "/" + coord.y + ".png";
                },
                tileSize: new google.maps.Size(256, 256),
                name: "OpenStreetMap",
                maxZoom: 18
            }));
          } else {
            map = new google.maps.Map(document.getElementById('map'),{
              center: new google.maps.LatLng (defLat, defLng),
              zoom: defaultZoom,
              mapTypeId: google.maps.MapTypeId.ROADMAP
            });
          }
          
        infoWindow = new google.maps.InfoWindow;
        
        var bounds = new google.maps.LatLngBounds();
        for (var index = 0; index < hostsInfo.length; ++index) {
          var host = hostsInfo[index];
          var hostState = host.state ? host.state.toUpperCase() : '';
          var hostGlobalState = -1;
          var gpsLocation = new google.maps.LatLng(host.lat, host.lng);
          var iconBase='host';
          if (debugJs) console.log('host '+host.name+' is '+hostState+', located here : '+gpsLocation);
          // if (hostState != 'UP') if (debugJs) console.error('host '+host.name+' is '+hostState+', located here : '+gpsLocation);

          if (host.monitoring && host.monitoring == true) {
            iconBase='host-monitored';
            
            switch(hostState.toUpperCase()) {
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
            
            var infoViewContent = '';
            if (host.link != undefined) {
              infoViewContent += '<span class="map-hostname">'+'<a href="'+host.link+'">'+host.name+'</a>'+' is '+host.state+'.</span>';
            } else {
              infoViewContent += '<span class="map-hostname">'+host.name+' is '+host.state+'.</span>';
            }
            infoViewContent += '<hr/>';
            if (host.services != undefined) {
              infoViewContent += '<ul class="map-servicesList">';
              for (var idxServices = 0; idxServices < host.services.length; ++idxServices) {
                var service = host.services[idxServices];
                var serviceState = service.state.toUpperCase();
                // if (debugJs) console.log(' - service '+service.name+' is '+serviceState);
                // if (serviceState != 'OK') if (debugJs) console.error(' - service '+service.name+' is '+serviceState);
                infoViewContent += '<li title="'+service.event+'"><span class="map-service map-service-'+serviceState+'">&nbsp;</span>'+service.name+' is '+serviceState+'.</li>';

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
              if (host.linkServices != undefined) {
                infoViewContent += '<hr/>';
                infoViewContent += '<button><a href="'+host.linkServices+'">Services</a></button>';
              }
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
          allMarkers.push(markerCreate(host.name, markerState, infoViewContent, gpsLocation, iconBase));
          bounds.extend(gpsLocation);
        }
        map.fitBounds(bounds);
        
        var mcOptions = {
          zoomOnClick: true, showText: true, averageCenter: true, gridSize: 10, minimumClusterSize: 2, maxZoom: 18,
          styles: [
            { height: 50, width: 50, url: imagesDir+"/cluster-OK.png" },
            { height: 60, width: 60, url: imagesDir+"/cluster-WARNING.png" },
            { height: 60, width: 60, url: imagesDir+"/cluster-KO.png" }
          ]
          ,
          calculator: function(markers, numStyles) {
            // Manage markers in the cluster ...
            if (debugJs) console.log("marker, count : "+markers.length);
            // if (debugJs) console.log(markers);
            var clusterIndex = 1;
            for (i=0; i < markers.length; i++) {
              var currentMarker = markers[i];
              // if (debugJs) console.log("marker, "+currentMarker.hostname+" state is : "+currentMarker.hoststate);
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

        var oms = new OverlappingMarkerSpiderfier(map, {
          markersWontMove: true, 
          markersWontHide: true,
          keepSpiderfied: true,
          nearbyDistance: 10,
          circleFootSeparation: 50,
            spiralFootSeparation: 50,
            spiralLengthFactor: 20
        });

        oms.addListener('click', function(marker) {
          if (debugJs) console.log('click marker for host : '+marker.hostname);
          infoWindow.setContent(marker.iw_content);
          infoWindow.open(map, marker);
        });
        oms.addListener('spiderfy', function(markers) {
          if (debugJs) console.log('spiderfy ...');
          infoWindow.close();
        });
        oms.addListener('unspiderfy', function(markers) {
          if (debugJs) console.log('unspiderfy ...');
        });
        
        for (i=0; i < allMarkers.length; i++) {
          oms.addMarker(allMarkers[i]);
        }
      });
    });
  });

  return true;
};
