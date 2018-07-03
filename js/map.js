// Snazzy Map Style - https://snazzymaps.com/style/5/greyscale
var mapStyle = [{"featureType":"all","elementType":"all","stylers":[{"saturation":-100},{"gamma":0.5}]}];

function initialize() {	
	
	var myLatlng = new google.maps.LatLng(53.462560, -6.209538);
	var mapOptions = {
		zoom: 15,
		center: myLatlng,
		zoomControl: false, // change to "true" to see "+ / -" zoom controls
		scaleControl: false, // change to "true" to see the scale at the bottom of the map
		mapTypeControl: false, // change to "true" to see "Map / Satellite" trigger
		scrollwheel: false, // change to "true" to control zoom on mouse scroll
		streetViewControl: false, // change to "true" to see the Street View control
		styles: mapStyle
	}
	
	var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	
	overlay = new CustomMarker(
		myLatlng, 
		map,
		{
			marker_id: 'marker'
		}
	);
	
	// Center the map when browser is resized
	google.maps.event.addDomListener(window, "resize", function() {
		var center = map.getCenter();
		google.maps.event.trigger(map, "resize");
		map.setCenter(center); 
	});
}

google.maps.event.addDomListener(window, 'load', initialize);


function CustomMarker(latlng, map, args) {
	this.latlng = latlng;	
	this.args = args;	
	this.setMap(map);	
}

CustomMarker.prototype = new google.maps.OverlayView();

CustomMarker.prototype.draw = function() {
	
	var self = this;
	
	var div = this.div;
	
	if (!div) {
	
		div = this.div = document.createElement('div');
		
		div.className = 'marker';
		
		div.style.position = 'absolute';
		
		if (typeof(self.args.marker_id) !== 'undefined') {
			div.dataset.marker_id = self.args.marker_id;
		}
		
		var panes = this.getPanes();
		panes.overlayImage.appendChild(div);
	}
	
	var point = this.getProjection().fromLatLngToDivPixel(this.latlng);
	
	if (point) {
		div.style.left = (point.x - 50) + 'px';
		div.style.top = (point.y - 50) + 'px';
	}
};

CustomMarker.prototype.remove = function() {
	if (this.div) {
		this.div.parentNode.removeChild(this.div);
		this.div = null;
	}	
};

CustomMarker.prototype.getPosition = function() {
	return this.latlng;	
};