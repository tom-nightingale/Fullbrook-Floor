
import 'leaflet/dist/leaflet';

//Starting map pointer restAPI call
(function ($) {
  "use strict";
  // add all of your code within here, not above or below
  $(function () {
    $(document).ready(function () {

      if ($('#leafletmap').length) {
 
        var lat = $('#leafletmap').data('lat');
        var long = $('#leafletmap').data('long');
        var marker_title = $('#leafletmap').data('marker-title');

        var mapZoom = 15;

        var propertyMap = L.map('leafletmap').setView([lat, long], mapZoom);
        
        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
          attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
          accessToken: 'pk.eyJ1IjoibGVhZ3VlZGlnaXRhbCIsImEiOiJja2RnZXVwcmIyNGs0MnFyeDVtNGgyOGViIn0.aYADlP-55VjTyYEHsnjCRA',
          id: 'mapbox/streets-v11',
          tileSize: 512,
          scrollWheelZoom: false,
          minZoom: 10,
          maxZoom: 18,
          zoomOffset: -1,
        }).addTo(propertyMap);

        //Generate our MPG icon
        var myIcon = L.icon({
          iconUrl: themeURL.themeURL+'/images/map-marker.svg',
          iconSize: [63, 58],
          iconAnchor: [31, 29],
          popupAnchor: [0, -28],
          shadowSize: [68, 95],
          shadowAnchor: [22, 94]
        });

        var marker = L.marker([lat, long], { icon: myIcon }).addTo(propertyMap);
        marker.bindPopup('<b>' + marker_title + '</b>'); 

      }//endif check for map div

    });
  });
}(jQuery));

