;
/**
 * leaflet.js
 * openstreetmap
 * @date 21.02.2019
 * @author @cla
 * 
 * !!!!!
 * TODO: move the if (window.isSinglePatient !== 1) outside of the object
 * !!!!
 *
 */
if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
    console.info('custom view js included : ' + document.currentScript.src);
}




/*
 *
 * ====================================================================================
 * ========== OSRM "service"  : route , nearest , table , match , trip , tile =========
 * ====================================================================================
 * 
 * ====================================================================================
 * ================================== service:route ===================================
 * ====================================================================================
 * Option 	Values 	Description
 * alternatives 	true , false (default) 	Search for alternative routes and return as well. *
 * steps 	true , false (default) 	Return route steps for each route leg
 * annotations 	true , false (default) 	Returns additional metadata for each coordinate along the route geometry.
 * geometries 	polyline (default), polyline6 , geojson 	Returned route geometry format (influences overview and per step)
 * overview 	simplified (default), full , false 	Add overview geometry either full, simplified according to highest zoom level it could be display on, or not at all.
 * continue _ straight 	default (default), true , false 	Forces the route to keep going straight at waypoints constraining uturns there even if it would be faster. Default value depends on the profile.
 * 
 * ====================================================================================
 * =================================== service:trip ===================================
 * ====================================================================================
 * Option 	Values 	Description
 * steps 	true , false (default) 	Return route instructions for each trip
 * annotations 	true , false (default) 	Returns additional metadata for each coordinate along the route geometry.
 * geometries 	polyline (default), polyline6 , geojson 	Returned route geometry format (influences overview and per step)
 * overview 	simplified (default), full , false 	Add overview geometry either full, simplified according to highest zoom level it could be display on, or not at all.
 *
 */

/**
 * TODO: delete
var _orsm_service = 'route';
var ispc_osm_server = "http://10.0.0.16";

var __L_Configs = {
	//mod_tile > renderd confif
	'tiles': {
		serviceUrl : ispc_osm_server + '/pngtiles/{z}/{x}/{y}.png'
	},
		
    //OSRM config
    'osrm': {
        serviceUrl: ispc_osm_server + '/trip/v1', //trip || route
        profile: 'car', //car|driving (bicycle & foot - you have to compile files for them if you need to use) 
        timeout: 30 * 1000,

        /**
         * service:route
         *\/
        /**
        routingOptions: {
        	
        	//alternatives & steps were hardcoded as true into leaflet-routing-machine.js ... @cla made some changes to that file
        	alternatives : false,//	true , false (default) 	Search for alternative routes and return as well. *
        	steps : false, //	true , false (default) 	Return route steps for each route leg
        	
        	annotations : false,// 	true , false (default) 	Returns additional metadata for each coordinate along the route geometry.
        	
        	//next 2 work in tandem.. also have different names in Leaflet
        	geometries : 'polyline',//	polyline (default), polyline6 , geojson 	Returned route geometry format (influences overview and per step)
        	overview : 'simplified',//	simplified (default), full , false 	Add overview geometry either full, simplified according to highest zoom level it could be display on, or not at all.
        	
        	//Leaflet uses option.allowUTurns = continue_straight
        	continue_straight : 'default', //	default (default), true , false 	Forces the route to keep going straight at waypoints constraining uturns there even if it would be faster. Default value depends on the profile.
        },

        requestParameters : {
        	//alternatives : false
        	//steps : false
        	annotations : true
        },
        */


        /**
         * service:trip
         *\/
        routingOptions: {
            alternatives: null,
            steps: true
        },
        /*
        requestParameters : {
        },
        *\/


        polylinePrecision: 5,
        useHints: false,
        suppressDemoServerWarning: false,
        language: 'en' //de if you likeit hardcore
    },
    //Nominatim Config
    'nominatim': {
        serviceUrl: ispc_osm_server + '/nominatim/',
        /*
	    serviceUrl: '//nominatim.openstreetmap.org/',
	    geocodingQueryParams: {},
	    reverseQueryParams: {},
	    htmlTemplate: function(r) {...}
		*\/
    }
};
*/





$(document).ready(function() {


	//console.log(window.__L_Configs);
	//console.log(window.jsonCenterLocation);
	//console.log(window.jsonUserPatients);
	//console.log(window.jsonActivePatients);
	
	// TODO-2247 - changes done by Ancuta - 17.04.2019 - to make the PATIENT OSRM - similar to google  
	var start_zoom = 6;
	var _maxLCircleExtrasize = 10000;
	if(window.isSinglePatient == 1){
		start_zoom = 15;
		 _maxLCircleExtrasize = 1;
	}
	//-- 
	
	MapHelper.init('map', {
        zoom		: start_zoom, //start map with this zoom
        centerLocation : window.jsonCenterLocation,
        maxLCircleExtrasize : _maxLCircleExtrasize,
        
        tiles		: __L_Configs.tiles,
        geocoder	: __L_Configs.geocoder,
        router		: __L_Configs.router,
        	
    });
	
	
	$.each(window.jsonUserPatients, function(i, patient) { 
		MapHelper.addMarker(patient);
	});
	
	
	/**
	 * set a default list of waypoint so you have a route
	 */
	//MapHelper.setWaypoints( __waypoints);
	
	return;
	
	
});















MapHelper = (function ($) {
    'use strict';

    var settings = {
    	
		centerLocation : {},
		
        zoom		: 10,
        maxLCircleExtrasize : 10000,//in meters
        
        tiles		: null,
        geocoder	: null,
        router		: null
    };
    
    

    var mapId = 'map';
    var map = null;
    var baseMaps = {};
    var routingControl = null;
    var geocoder = null;
    var waypoints = [];
    var selectedPatients = [];
    var selectedRouteplan = null;
    var markersCluster = null;
    var listMarkersControll = null;
    var maxLCircle = null;
    var zoomClusterTimeout = null;


    var init = function (mapLayerId, options) {
        
    	settings = $.extend(settings, options);
    	
    	mapId = mapLayerId || mapId;
        
        initMap(this);
        
    };
    
    var panMap = function (lat, lng) {
        map.panTo(new L.LatLng(lat, lng));
    };

    var centerMap = function (e) {
        panMap(e.latlng.lat, e.latlng.lng);
    };

    var zoomIn = function (e) {
        map.zoomIn();
    };

    var zoomOut = function (e) {
        map.zoomOut();
    };
    

    var getMap = function () {
        return map;
    };

    

    

    
    /**
     * routingControl set plan's waypoints
     */
    var setWaypoints = function (_waypoints) {
    	
    	waypoints = _waypoints || waypoints;
    	
    	if (routingControl != null) {
    	
    		routingControl.getPlan().setWaypoints(waypoints);
    		
    		__resizeCenterCircle();
    	}
    };
    
    var __resetMapMarkersAndRoute = function () {
    	
    	markersCluster.clearLayers();
    	
    	waypoints = [];
    	
    	selectedPatients = [];
    	
    	addMarker(settings.centerLocation);
    	
    	setWaypoints();
    	
    };
    
    
    /**
     * a red cicle with the center at the centerLocation = user or hospital location
     */
    var __initCenterCircle = function() {
        
    	// TODO-2247 - changes done by Ancuta - 18.04.2019 - to make the PATIENT OSRM - similar to google
    	// do not show red circle in patient
    	if (maxLCircle == null) {
	        maxLCircle = L.circle(settings.centerLocation.waypoint.latLong, settings.maxLCircleExtrasize, {
	        	color: "#99003f", opacity:.9, //stroke
	        	fill : false, // or add some color inside the circle fillColor: "#b3bed7", fillOpacity : .2
	        }).addTo(map);
    	}
    	
        __resizeCenterCircle();
    }; 
    
    
    var __resizeCenterCircle = function() {
    	
    	if (settings.centerLocation.waypoint.latLong) {	
	    	var _farthestDistance = 0,
	    	_distance2center = 0;
	    	
	    	$.each(waypoints, function(i, waypoint) {
	    		_distance2center = getDistance(waypoint, settings.centerLocation.waypoint.latLong);
	    		_farthestDistance = _distance2center > _farthestDistance ? _distance2center : _farthestDistance;
	    	});
	    	
			maxLCircle.setRadius(_farthestDistance + settings.maxLCircleExtrasize);
    	}
    };
    
    /**
     * add one patient as marker
     * TODO remove type.. we have now poi.type
     */
    var addMarker = function (poi) {
    	
    	if ( ! __parseMarkerLatLong(poi)) {
    		return false; // it may be added later via a callback
    	}
    	
    	
    	if (poi.isMapCenter === 1) {
    		
    		/*
    		 * center is allways in route
    		 */
    		settings.centerLocation.waypoint.latLong = poi.waypoint.latLong;
    		
    		waypoints.unshift(settings.centerLocation.waypoint.latLong);
    		
    		map.panTo(settings.centerLocation.waypoint.latLong);
    		
    		__initCenterCircle();
    		
    		setWaypoints();
    		
    	} else if (poi.isInRoute === 1) {
    		
    		waypoints.push(poi.waypoint.latLong);
            selectedPatients.push(poi);
            setWaypoints();
    	}
    	
    	
    	var _popupInner = L.DomUtil.create('div', 'infoPopup' + poi.type);
    	_popupInner.innerHTML = __formatPopupHtml(poi);
//    	
    	var _popup = new L.Popup({'autoClose': false, minWidth : 200});
    	_popup.setContent(_popupInner);
    	
    	
    	// TODO-2247 - changes done by Ancuta - 17.04.2019 - to make the PATIENT OSRM - similar to google
    	// do not show add on route if in patient
    	// if in patient show red marker - no icon
    	var shape4patient = 'square';
    	var color4patient = 'yellow markerPatient';
    	var add2route_option =  '<span class="wrapper_switch_to_route"><input type="checkbox" value="1" class="add2route"></span>';
        if (window.isSinglePatient == 1){
        	shape4patient = 'circle';
        	color4patient = 'orange-dark';
        	add2route_option =  '';
        } 
        // --
    	
    	var _marker = L.marker(poi.waypoint.latLong, {

            draggable: false, //(i == 0 ? false : true),

            //title : poi.title - this was used as default.. but ugly html title was displayed..
            listMarkersLabel: poi.type == "patient" ? add2route_option + poi.title : poi.title, //this are used to create the list items
            listSearchLabel: poi.type == "patient" ? poi.nice_name_epid : poi.title, //search is performed against this text

            icon: L.ExtraMarkers.icon({
                //Additional classes in the created <i> tag fa-rotate90 myclass; space delimited classes to add
                //extraClasses: poi.type == "patient" ? 'fa-spin' : 'xxxxxxxxx' ,
                
            	//Name of the icon with prefix
                //icon: poi.type == "patient" ? 'patient_status_i patient_status_green ' + poi.status_icon : 'fa-number',
                
            	//Color of the icon 	'white' 	'white', 'black' or css code (hex, rgba etc)
                iconColor: "#ffffff",
                
                //Custom HTML code 	'' 	<svg>, images, or other HTML; a truthy assignment will override the default html icon creation behavior
                innerHTML: __formatPopupIcon(poi),
                
                //Color of the marker (css class) 'red', 'orange-dark', 'orange', 'yellow', 'blue-dark', 'cyan', 'purple', 'violet', 'pink', 'green-dark', 'green', 'green-light', 'black', or 'white'
               	markerColor: poi.type == "patient" ?  color4patient : 'blue markerCenter',
                
                // 	Instead of an icon, define a plain text 	'' 	'1' or 'A', must set icon: 'fa-number'
                //number: poi.type == "patient" ?  null : "H",
                
                //The icon library's base class ,'glyphicon' fa (see icon library's documentation)
                prefix: poi.type == "patient" ? 'fa' :  '',
                
                //Shape of the marker (css class) 'circle', 'square', 'star', or 'penta'
                shape: poi.type == "patient" ? shape4patient : 'star',
            }),
            'data': poi,
        })
        .bindPopup(_popup)
        .bindTooltip(poi.title)
        //.on('click', markerOnClick)
        .on('mouseover', function(e) {
            if (this._icon != null) {
                this._icon.classList.remove("prop-div-icon");
                this._icon.classList.add("prop-div-icon-shadow");
            }
        })
        .on('mouseout', function(e) {
            if (this._icon != null) {
                this._icon.classList.remove("prop-div-icon-shadow");
                this._icon.classList.add("prop-div-icon");
            }
        });
	    
    	
	    //_marker.addTo(markersCluster);
	    markersCluster.addLayer(_marker);
	    
	    
	    $("input.add2route[type=checkbox]", _popup._content).switchButton({
	        on_label: translate('Add to route'),
	        off_label: translate('Not in route'),
	        clear: true
	    }).on('change', function() {

	        if (this.checked) {
	            waypoints.push(poi.waypoint.latLong);
	            selectedPatients.push(poi);
	        } else {
	            waypoints = jQuery.grep(waypoints, function(value) {
	                return JSON.stringify(value) != JSON.stringify(poi.waypoint.latLong);
	            });
	            selectedPatients = jQuery.grep(selectedPatients, function(patient) {
	            	return JSON.stringify(patient.waypoint.latLong) != JSON.stringify(poi.waypoint.latLong) ;
	            });
	        }

	        try{
	        	listMarkersControll._updateList();	        	
	        } catch (e){}
	        
	        setWaypoints();
	    });
		
    };
    
    
    var __formatPopupHtml = function (poi) {
    	
    	var htmlString = '';
    	
    	// TODO-2247 - changes done by Ancuta - 17.04.2019 - to make the PATIENT OSRM - similar to google
    	var add2route_option_popup =  '<span class="wrapper_switch_to_route"><input type="checkbox" value="1" class="add2route"></span>';
        if (window.isSinglePatient == 1){
        	add2route_option_popup =  '';
        } 
    	// --
    	switch (poi.type) {
    	
			case 'patient' :

				htmlString =  "<strong>" + poi.title + "</strong><br>"
		    	+ (poi.description && poi.description.length ? poi.description + '<br>': '')
		    	+ (poi.enc_id && poi.enc_id.length ? '<a href="' + appbase + 'patientcourse/patientcourse?id='+poi.enc_id+'" target="_blank">' + translate("Open patient course")  + "</a><br>" : '')
		    	+ add2route_option_popup;
		    	
			break;
				
	    	case 'user' :
	    		htmlString = "<strong>" + poi.title + "</strong><br>"
		    	+ '<a href="' + appbase + 'user/editprofile" target="_blank">' + translate("User profile") + "</a><br>"
	        	+ (poi.description && poi.description.length ? poi.description + '<br>': '');
    		break;
    		
	    	case 'hospital' :
	    	default :
	    		htmlString = "<strong>" + poi.title + "</strong><br>"
	        	+ (poi.description && poi.description.length ? poi.description + '<br>': '');
			break;
			
    	}
	
    	return htmlString;
    };
    
    var __formatPopupIcon = function (poi) {
    	
    	var htmlString = '';
    	
    	switch (poi.type) {
    	
			case 'patient' :
				if (poi.status_icon) {		
					//style="background:#' + poi.status_icon.color 
					htmlString =  '<div class="marker_custom_image">'
	                + "<img src='" + appbase + "icons_system/" + poi.status_icon.image + "'>"
	                + "</div>";
				}
			break;
				
			case 'hospital' :
	    	case 'user' :
	    		htmlString =  '<div class="marker_center_text">' + poi.title + '</div>';
    		break;
    		
    		
			break;
			
    	}
	
    	return htmlString;
    };
    
    
    var __parseMarkerLatLong = function (poi) {
    		
		if (poi.waypoint.hasOwnProperty('latLong')
			&& poi.waypoint.latLong instanceof Array
			&& poi.waypoint.latLong != null 
			&& poi.waypoint.latLong.length === 2) 
		{
			return true;
			
		} else if (poi.waypoint.address != null  && poi.waypoint.address.length > 2) {				

			geocoder.geocode(poi.waypoint.address, __geocoderCallback(poi));
			
			return false;
			
		} else {
			
			return false; //throw "Invalid address, cannot geocode";
		}
		
		return false;
    };
    
    /**
     * TODO add as L.
     */
    var __geocoderCallback = function (poi) {
    	
		var geocodeCallBack = function(a, b) {
			
			if (a[0] && a[0].properties) {
			
				poi.waypoint.latLong = [Number(a[0].properties.lat), Number(a[0].properties.lon)];
		        	
	        	addMarker(poi);
		       
			} else {
				//bad address...
				wrongAddresses(poi);
			}
	    };
	    
	    return geocodeCallBack;
    };

    
    
    
    var getDistance = function(origin, destination) {
        // return distance in meters
        var lon1 = toRadian(origin[1]),
            lat1 = toRadian(origin[0]),
            lon2 = toRadian(destination[1]),
            lat2 = toRadian(destination[0]);

        var deltaLat = lat2 - lat1;
        var deltaLon = lon2 - lon1;

        var a = Math.pow(Math.sin(deltaLat/2), 2) + Math.cos(lat1) * Math.cos(lat2) * Math.pow(Math.sin(deltaLon/2), 2);
        var c = 2 * Math.asin(Math.sqrt(a));
        var EARTH_RADIUS = 6371;
        return c * EARTH_RADIUS * 1000;
    };
    var toRadian = function(degree) {
        return degree*Math.PI/180;
    };
    

    var initMap = function (that) {

    	var $this = that;

        /**
         * init Leaflet map
         */
        map = L.map(mapId, {
    		attributionControl: false,
    		
    		//center : settings.centerLocation.waypoint.latLong || [52.520008, 13.404954], //default to Berlin until map is loaded
    		center : settings.centerLocation.waypoint.latLong || [52.480188, 13.449773], //default to Berlin until map is loaded
    		
    		zoom: settings.zoom,
    		minZoom : 5,
    		maxZoom : 18, // this is the max from our server config
//    		maxBounds
//    		crs
//    		contextmenu: true,
//    		contextmenuWidth: 140
    	});

        
        L.Icon.Default.imagePath = 'images';
        
        /**
         * add ISPC L.tileLayer
         */
        
//        var debugserviceUrl = "http://10.0.0.16/pngtiles/{z}/{x}/{y}.png" ;
//        settings.tiles.serviceUrl  = debugserviceUrl;
        	
        baseMaps["OSM_ISPC"] = L.tileLayer(settings.tiles.serviceUrl, {
            attribution : '&copy; <a href="http://osm.org/copyright">OpenStreetMap by ISPC</a>', //attributionControl is false, this is not shown.. use onmapready to attach
            maxZoom: 18, //this is the max from our server config
        }).addTo(map);
        
//       
		/*
        var vectorTileOptions = {
        		maxNativeZoom: 14,
        		
        	    vectorTileLayerStyles: {
        	        // A plain set of L.Path options.
        	        landuse: {
        	            weight: 0,
        	            fillColor: '#9bc2c4',
        	            fillOpacity: 1,
        	            fill: true
        	        },
        	        // A function for styling features dynamically, depending on their
        	        // properties and the map's zoom level
        	        admin: function(properties, zoom) {
        	            var level = properties.admin_level;
        	            var weight = 1;
        	            if (level == 2) {weight = 4;}
        	            return {
        	                weight: weight,
        	                color: '#cf52d3',
        	                dashArray: '2, 6',
        	                fillOpacity: 0
        	            }
        	        },
        	        // A function for styling features dynamically, depending on their
        	        // properties, the map's zoom level, and the layer's geometry
        	        // dimension (point, line, polygon)
        	        water: function(properties, zoom, geometryDimension) {
        		    if (geometryDimension === 1) {   // point
        		        return ({
        	                    radius: 5,
        	                    color: '#cf52d3',
        	                });
        		    }
        		    
        		    if (geometryDimension === 2) {   // line
        	                 return ({
        	                    weight: 1,
        	                    color: '#cf52d3',
        	                    dashArray: '2, 6',
        	                    fillOpacity: 0
        	                });
        		    }
        		    
        		    if (geometryDimension === 3) {   // polygon
        		         return ({
        	                    weight: 1,
        	                    fillColor: '#9bc2c4',
        	                    fillOpacity: 1,
        	                    fill: true
        	                });
        		    }
        	        },
        	        // An 'icon' option means that a L.Icon will be used
        	        place: {
        	            icon: new L.Icon.Default()
        	        },
        	        road: []
        	    }
        	};
		*/
//		L.vectorGrid.protobuf("https://maps.smart-q.de/osrm/tile/v1/car/tile({x},{y},{z}).mvt", vectorTileOptions)
//		.addTo(map);
        /**
         * add  L.Control.Geocoder
         */
        geocoder = L.Control.Geocoder.nominatim(settings.geocoder);
        
        /**
         * add button to toggle popups of markers
         */
        
        L.easyButton({
            states: [{
                    stateName: 'showMarkerPupupsInView',        // name the state
                    icon:      'fa-eye-closed',               // and define its properties
                    title:     translate('Display Info'),      // like its title
                    onClick: function(btn, map) {       // and its callback
                    	
                    	//showMarkerPupupsInView
                    	map.eachLayer(function(layer) {
                            if (layer instanceof L.Marker) {
                                if (map.getBounds().contains(layer.getLatLng())) {
                                    layer.openPopup();
                                }
                            }
                        });
                    	
                        btn.state('hideAllMarkerPupups');    // change state on click!
                    }
                }, {
                    stateName: 'hideAllMarkerPupups',
                    icon:      'fa-eye-opened',
                    title:     translate('Hide Info'),
                    onClick: function(btn, map) {
                    	
                    	//hideAllMarkerPupups
                    	map.eachLayer(function(layer) {
                            if (layer instanceof L.Marker) {
                            	layer.closePopup();
                            }
                        }); 
                        btn.state('showMarkerPupupsInView');
                    }
            }]
        
        }).addTo( map );
        
        
        
        /**
         * add button to toggle show All patients/ Only my user's patients
         * TODO : move to fn and call with var
         */ 
        if (window.isSinglePatient !== 1)
	        L.easyButton({
	            states: [{
	                    stateName: 'showOnlyMyPatients',        // name the state
	                    icon:      'fa-my-patients',               // and define its properties
	                    title:     translate('Display All Patients'),      // like its title
	                    onClick: function(btn, map) {       // and its callback
	
	                    	__resetMapMarkersAndRoute();
	                    	
	                    	$.each(window.jsonActivePatients, function(i, patient) { 
	                    		addMarker(patient);
	                    	});
	                    	
	                    	
	                        btn.state('showAllActivePatients');    // change state on click!
	                    }
	                }, {
	                    stateName: 'showAllActivePatients',
	                    icon:      'fa-all-patients',
	                    title:     translate('Display only my patients'),
	                    onClick: function(btn, map) {
	                    	
	                    	__resetMapMarkersAndRoute();
	                    	
	                    	$.each(window.jsonUserPatients, function(i, patient) { 
	                    		addMarker(patient);
	                    	});
	                    	
	                        btn.state('showOnlyMyPatients');
	                    }
	            }]
	        
	        }).addTo( map );
    
        
        
        
        
        /**
         * add button to export waypoints
         */
        if (window.isSinglePatient !== 1)
	        L.easyButton({
	            states: [{
	                    icon:      'fa-export',           
	                    title:     translate('Save as csv'),
	                    onClick: function(btn, map) { 
	
	                    	if (window.FileReader !== undefined && window.Blob) 
	                    	{
	                    		//as csv
	                    		var __output = _exportFormat();
	                    		__output = '\ufeff' + __output;
	                			
	                    		var __charset = document.characterSet || document.charset;
	                			if ( __charset ) {
	                				__charset = ';charset='+__charset;
	                			}
	//                    		
	                			
	                			
	                			$this.fileSaveHelper(
	                					new Blob( [__output], {type: 'text/csv'+__charset} ),
	                    				'ispc_route.csv',
	                    				false
	                			);
	                			
	                    	} else {
	                    		//copy to clipboard
	                    	}
	                    }
	                }]
	        }).addTo( map );
        
        
        /**
         * add L.Routing.control
         */
        routingControl =  L.Routing.control(L.extend(settings.router, {
        	
        	plan: L.Routing.plan(waypoints, {
        		
        		createMarker: function() { return null; },
        		
    			geocoder: geocoder,
    			
    			//TODO move this to settings.routing
    	        routeWhileDragging: true,    	        
    	        reverseWaypoints: true,
    	        
    	        /**
    	         * this 2 don't work on plan.. plan resets the zoom... why ???
    	         */
    	        //fitSelectedRoutes : false,//'smart',
    	        //z: 10,
    	        
	    	        showAlternatives: true,
    	        //autoRoute : false,
    	        
    	        altLineOptions: {
    	            styles: [{
    	                    color: 'black',
    	                    opacity: 0.15,
    	                    weight: 9
    	                },
    	                {
    	                    color: 'white',
    	                    opacity: 0.8,
    	                    weight: 6
    	                },
    	                {
    	                    color: 'blue',
    	                    opacity: 0.5,
    	                    weight: 2
    	                }
    	            ]
    	        }
    		}),
        })).addTo(map);
    	
    	L.Routing.errorControl(routingControl).addTo(map);
	
    	
    	routingControl.on('routesfound', function (e) {
    		selectedRouteplan = e.routes[0];
    		//	waypoints = e.routes[0].waypoints;
    		//  distance = e.routes[0].summary.totalDistance;
    		//  time = e.routes[0].summary.totalTime;
		});
    	
    	
    	/**
    	 * markers are clustered, because it can happen to have multiple patients at the same location
    	 */
    	markersCluster = L.markerClusterGroup({
    		spiderLegPolylineOptions : { weight: 1.5, color: '#f00', opacity: 0.8 },
    		spiderfyDistanceMultiplier : 1,
    		maxClusterRadius : 20,
    		spiderfyOnMaxZoom : true,
    	})
    	.on('clustermouseover', function (e) {
    		// e.layer is a cluster
    		//console.log('cluster ' + e.layer.getAllChildMarkers().length);
    		_fireEventOnMarkerOrVisibleParentCluster(e.layer, 'mouseover');
    	})
    	.on('layeradd', function (e){
    		clearTimeout(zoomClusterTimeout);
    		// 	 TODO-2247 - changes done by Ancuta - 17.04.2019 - to make the PATIENT OSRM - similar to google 
    		// do not zoom to maxZoom - rmeain ar start zoom
    		if (window.isSinglePatient !== 1){
    		zoomClusterTimeout = setTimeout(function(){
    			var _bounds = markersCluster.getBounds();
    			if (_bounds.hasOwnProperty('_northEast') &&  _bounds.hasOwnProperty('_southWest'))
    				map.fitBounds(markersCluster.getBounds());
    			//map.panTo(settings.centerLocation.waypoint.latLong);
    		}, 1000);
    		}
    		
    	})
    	.addTo(map);
        
    	/**
    	 * add as first marker the centerLocation
    	 */
    	addMarker(settings.centerLocation);
        
    	/**
    	 * the search icon on the top left
    	 */
    	if (window.isSinglePatient !== 1)
	        map.addControl(new L.Control.Search({
	        	layer: markersCluster, 
	        	position : 'topleft' , 
	        	initial: false,
	        	propertyName: 'listSearchLabel',
	        	textPlaceholder: translate("Search..."),
	        	textErr: translate('Location not found'),	//error message
	    		textCancel: translate('Cancel'),
	        }));     
        
        /**
         * the list with patients from bottom left
         */
        listMarkersControll = (new L.Control.ListMarkers({
        	
            layer: markersCluster,
            itemIcon: null,
            itemArrow : '',
            label: 'listMarkersLabel',
            maxZoom : null //so we just pan
        })
    	.on('item-updatelist', function(e) {
        	$("input.add2route[type=checkbox]", $(e.list.children)).switchButton({
        		show_labels : false,
    	        clear: false
    	    });
        })
        /*
        .on('item-createItem', function(list) {
        	$("input.add2route[type=checkbox]", $(list.children)).switchButton({
        		show_labels : false,
        		clear: false
        	});
        })
        */
        .on('item-click', function(obj) {
        	//TODO attach click
        	var _layer = obj.layer,
        	_event = obj.event;
        	if ($(_event.target).parents('.wrapper_switch_to_route').length) {
//    		if ($(_event.explicitOriginalTarget).parents('.wrapper_switch_to_route').length || $(_event.target).parents('.wrapper_switch_to_route').length) {
        		var _checked = $(_event.target).parents('.wrapper_switch_to_route').find("input.add2route").prop('checked');
        		//$(_event.explicitOriginalTarget).parents('.wrapper_switch_to_route').find("input.add2route").prop('checked'); 
        		var _popupContent = _layer.getPopup().getContent();
        		
        		$(_popupContent).find('input.add2route')
        		.switchButton({'checked' : _checked}) 
        		;
        		
        		L.DomEvent.stopPropagation(_event);
        		   		
	        	return false; //return false so we don;t move the map
	        	
        	}
        	
        	return true;
        	
        })
        .on('item-mouseover', function(e) {
        	map.scrollWheelZoom.disable();
            // TODO : Trigger ClusteredMarker Event "mouseover"
            _fireEventOnMarkerOrVisibleParentCluster(e.layer, 'mouseover');
            return;
        })
        .on('item-mouseout', function(e) {
        	map.scrollWheelZoom.enable();
            // TODO : Trigger ClusteredMarker Event "mouseout"
            _fireEventOnMarkerOrVisibleParentCluster(e.layer, 'mouseout');
            return;
        }))        
        ;

        map.addControl(listMarkersControll);
        
    };

    
    /**
     * clustered markers spiderify
     */
    var _fireEventOnMarkerOrVisibleParentCluster = function(marker, eventName) {
        if (eventName === 'mouseover') {
            var visibleLayer = markersCluster.getVisibleParent(marker);

            if (visibleLayer instanceof L.MarkerCluster) {
                // We want to show a marker that is currently hidden in a cluster.
                // Make sure it will get highlighted once revealed.
                markersCluster.once('spiderfied', function() {
                    marker.fire(eventName);
                });
                // Now spiderfy its containing cluster to reveal it.
                // This will automatically unspiderfy other clusters.
                visibleLayer.spiderfy();
            } else {
                // The marker is already visible, unspiderfy other clusters if
                // they do not contain the marker.
                __unspiderfyPreviousClusterIfNotParentOf(marker);
                marker.fire(eventName);
            }
        } else {
            // For mouseout, marker is necessarily unclustered already.
            marker.fire(eventName);
        }
    };
    var __unspiderfyPreviousClusterIfNotParentOf = function(marker) {
        // Check if there is a currently spiderfied cluster.
        // If so and it does not contain the marker, unspiderfy it.
        var spiderfiedCluster = markersCluster._spiderfied;

        if (
            spiderfiedCluster &&
            !__clusterContainsMarker(spiderfiedCluster, marker)
        ) {
            spiderfiedCluster.unspiderfy();
        }
    };
    var __clusterContainsMarker = function(cluster, marker) {
        var currentLayer = marker;

        while (currentLayer && currentLayer !== cluster) {
            currentLayer = currentLayer.__parent;
        }
        // Say if we found a cluster or nothing.
        return !!currentLayer;
    };
    	
    
    /**
     * from https://cdn.datatables.net/buttons/1.5.4/js/buttons.html5.js
     */
    var _newLine = function ( config )
    {
    	return config.newline ?
    		config.newline :
    		navigator.userAgent.match(/Windows/) ?
    			'\r\n' :
    			'\n';
    };
    /**
     * from https://cdn.datatables.net/buttons/1.5.4/js/buttons.html5.js
     */
    var _exportData = function ( data, config )
    {
    	var newLine = _newLine( config );
    	var boundary = config.fieldBoundary;
    	var separator = config.fieldSeparator;
    	var reBoundary = new RegExp( boundary, 'g' );
    	var escapeChar = config.escapeChar !== undefined ?
    		config.escapeChar :
    		'\\';
    	var join = function ( a ) {
    		var s = '';

    		// If there is a field boundary, then we might need to escape it in
    		// the source data
    		for ( var i=0, ien=a.length ; i<ien ; i++ ) {
    			if ( i > 0 ) {
    				s += separator;
    			}

    			s += boundary ?
    				boundary + ('' + a[i]).replace( reBoundary, escapeChar+boundary ) + boundary :
    				a[i];
    		}
    		return s;
    	};

    	var header = config.header && data.header ? join( data.header )+newLine : '';
    	var footer = config.footer && data.footer ? newLine+join( data.footer ) : '';
    	var body = [];
    	
    	for ( var i=0, ien=data.body.length ; i<ien ; i++ ) {
    		body.push( join( data.body[i] ) );
    	}

    	return {
    		str: header + body.join( newLine ) + footer,
    		rows: body.length
    	};
    };

    
    var _exportFormat = function()
    {
    	var __patients = [],
    	__routeplanInputWaypoints = selectedRouteplan.inputWaypoints,
    	__routeplanTotalDistance = selectedRouteplan.summary.totalDistance,
    	__routeplanTotalTime = selectedRouteplan.summary.totalTime,
    	__routedPatients = {};
    	
    	/*
    	 * TODO @cla v2 .. export patients in the order they should be visited
//    	console.log(selectedRouteplan, __routeplanWaypoints, __routeplanTotalDistance, __routeplanTotalTime);
    	console.log(selectedRouteplan);
    	
    	$.each(__routeplanInputWaypoints, function(i, poi) {
    		
    		var _patients = jQuery.grep(selectedPatients, function(__pat) {
             	return JSON.stringify(__pat.waypoint.latLong) == JSON.stringify([poi.latLng.lat ,  poi.latLng.lng ]);
            });
    		
    		var _patientsPOI = {};
    		jQuery.each(selectedPatients, function(i, __pat) {
    			if (JSON.stringify(__pat.waypoint.latLong) == JSON.stringify([poi.latLng.lat ,  poi.latLng.lng ])) {
    				_patientsPOI[__pat.enc_id] = __pat;
    			}
    		});
    		
    		
    		jQuery.extend(__routedPatients, _patientsPOI);
    		
//    		console.log(_patient);
    	});
    	
    	
    	
    	console.log('clicked order:');
    	console.log(selectedPatients);
    	
    	console.log('routed order:');
    	console.log(__routedPatients);
    	
    	console.log("correct :", __routeplanInputWaypoints);
    	
    	return;
    	*/
    	
    	
    	$.each(selectedPatients, function(i, poi){
    		__patients.push(new Array(
    				poi.title === null ? '' : poi.title, 
    				poi.waypoint._address.street === null ? '' : poi.waypoint._address.street,
					poi.waypoint._address.zip === null ? '' : poi.waypoint._address.zip,
					poi.waypoint._address.city === null ? '' : poi.waypoint._address.city
			));
    	});
    	var csv = _exportData({
	    		'header': [translate('name'), translate('street'), translate('zip'), translate('city')],
	    		'body': __patients
    		}, 
    		{
    		fieldSeparator: ',',
    		fieldBoundary: '"',
    		escapeChar: '"',
    		charset: null,
    		header: true,
    		footer: false
		});
    	return csv.str;
    };
    
    return {
        init: init, 
        
        panMap: panMap, 
        getMap: getMap,
        
        addMarker: addMarker,
        setWaypoints: setWaypoints
        
    };
}(jQuery));




function wrongAddresses(poi) {
	
	if ($("#wrongaddresses").find("li:contains('" + poi.title + "'):contains('" + poi.waypoint.address + "')").length) {
		
	} else {
		$("<li />", { 'class': 'list-markers-li' , html : "<a target='_blank' href='" + window.appbase + "patientcourse/patientcourse?id=" + poi.enc_id +"'>" + poi.title + "</a>  " + poi.waypoint.address})
		.appendTo('#wrongaddresses>ul');
		
		$("#wrongaddresses").show();	
	}
	
}



/**
 * from https://cdn.datatables.net/buttons/1.5.4/js/buttons.html5.js
 */
var _saveAs = (function(view) {
	"use strict";
	// IE <10 is explicitly unsupported
	if (typeof view === "undefined" || typeof navigator !== "undefined" && /MSIE [1-9]\./.test(navigator.userAgent)) {
		return;
	}
	var
		  doc = view.document
		  // only get URL when necessary in case Blob.js hasn't overridden it yet
		, get_URL = function() {
			return view.URL || view.webkitURL || view;
		}
		, save_link = doc.createElementNS("http://www.w3.org/1999/xhtml", "a")
		, can_use_save_link = "download" in save_link
		, click = function(node) {
			var event = new MouseEvent("click");
			node.dispatchEvent(event);
		}
		, is_safari = /constructor/i.test(view.HTMLElement) || view.safari
		, is_chrome_ios =/CriOS\/[\d]+/.test(navigator.userAgent)
		, throw_outside = function(ex) {
			(view.setImmediate || view.setTimeout)(function() {
				throw ex;
			}, 0);
		}
		, force_saveable_type = "application/octet-stream"
		// the Blob API is fundamentally broken as there is no "downloadfinished" event to subscribe to
		, arbitrary_revoke_timeout = 1000 * 40 // in ms
		, revoke = function(file) {
			var revoker = function() {
				if (typeof file === "string") { // file is an object URL
					get_URL().revokeObjectURL(file);
				} else { // file is a File
					file.remove();
				}
			};
			setTimeout(revoker, arbitrary_revoke_timeout);
		}
		, dispatch = function(filesaver, event_types, event) {
			event_types = [].concat(event_types);
			var i = event_types.length;
			while (i--) {
				var listener = filesaver["on" + event_types[i]];
				if (typeof listener === "function") {
					try {
						listener.call(filesaver, event || filesaver);
					} catch (ex) {
						throw_outside(ex);
					}
				}
			}
		}
		, auto_bom = function(blob) {
			// prepend BOM for UTF-8 XML and text/* types (including HTML)
			// note: your browser will automatically convert UTF-16 U+FEFF to EF BB BF
			if (/^\s*(?:text\/\S*|application\/xml|\S*\/\S*\+xml)\s*;.*charset\s*=\s*utf-8/i.test(blob.type)) {
				return new Blob([String.fromCharCode(0xFEFF), blob], {type: blob.type});
			}
			return blob;
		}
		, FileSaver = function(blob, name, no_auto_bom) {
			if (!no_auto_bom) {
				blob = auto_bom(blob);
			}
			// First try a.download, then web filesystem, then object URLs
			var
				  filesaver = this
				, type = blob.type
				, force = type === force_saveable_type
				, object_url
				, dispatch_all = function() {
					dispatch(filesaver, "writestart progress write writeend".split(" "));
				}
				// on any filesys errors revert to saving with object URLs
				, fs_error = function() {
					if ((is_chrome_ios || (force && is_safari)) && view.FileReader) {
						// Safari doesn't allow downloading of blob urls
						var reader = new FileReader();
						reader.onloadend = function() {
							var url = is_chrome_ios ? reader.result : reader.result.replace(/^data:[^;]*;/, 'data:attachment/file;');
							var popup = view.open(url, '_blank');
							if(!popup) view.location.href = url;
							url=undefined; // release reference before dispatching
							filesaver.readyState = filesaver.DONE;
							dispatch_all();
						};
						reader.readAsDataURL(blob);
						filesaver.readyState = filesaver.INIT;
						return;
					}
					// don't create more object URLs than needed
					if (!object_url) {
						object_url = get_URL().createObjectURL(blob);
					}
					if (force) {
						view.location.href = object_url;
					} else {
						var opened = view.open(object_url, "_blank");
						if (!opened) {
							// Apple does not allow window.open, see https://developer.apple.com/library/safari/documentation/Tools/Conceptual/SafariExtensionGuide/WorkingwithWindowsandTabs/WorkingwithWindowsandTabs.html
							view.location.href = object_url;
						}
					}
					filesaver.readyState = filesaver.DONE;
					dispatch_all();
					revoke(object_url);
				}
			;
			filesaver.readyState = filesaver.INIT;

			if (can_use_save_link) {
				object_url = get_URL().createObjectURL(blob);
				setTimeout(function() {
					save_link.href = object_url;
					save_link.download = name;
					click(save_link);
					dispatch_all();
					revoke(object_url);
					filesaver.readyState = filesaver.DONE;
				});
				return;
			}

			fs_error();
		}
		, FS_proto = FileSaver.prototype
		, saveAs = function(blob, name, no_auto_bom) {
			return new FileSaver(blob, name || blob.name || "download", no_auto_bom);
		}
	;
	// IE 10+ (native saveAs)
	if (typeof navigator !== "undefined" && navigator.msSaveOrOpenBlob) {
		return function(blob, name, no_auto_bom) {
			name = name || blob.name || "download";

			if (!no_auto_bom) {
				blob = auto_bom(blob);
			}
			return navigator.msSaveOrOpenBlob(blob, name);
		};
	}

	FS_proto.abort = function(){};
	FS_proto.readyState = FS_proto.INIT = 0;
	FS_proto.WRITING = 1;
	FS_proto.DONE = 2;

	FS_proto.error =
	FS_proto.onwritestart =
	FS_proto.onprogress =
	FS_proto.onwrite =
	FS_proto.onabort =
	FS_proto.onerror =
	FS_proto.onwriteend =
		null;

	return saveAs;
}(
	   typeof self !== "undefined" && self
	|| typeof window !== "undefined" && window
	|| this.content
));


MapHelper.fileSaveHelper = _saveAs;
