<?php
include('get_cicero_info.php');
?>

<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100%; width: 100%; }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
    </style>
    <script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js?sensor=false">
    </script>
    <script type="text/javascript">
      function initialize() {
        var map_data_from_php = <?php echo $map_data_for_javascript; ?> ;
        var center_lat = map_data_from_php.center_lat_long.lat_y;
        var center_long = map_data_from_php.center_lat_long.long_x;
        var azavea = new google.maps.LatLng(center_lat, center_long);
        var imageBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(map_data_from_php.district_map_image_data.extent.y_min,
                                map_data_from_php.district_map_image_data.extent.x_min),
        new google.maps.LatLng(map_data_from_php.district_map_image_data.extent.y_max,
                                map_data_from_php.district_map_image_data.extent.x_max));

        var mapOptions = {
          zoom: 13,
          maxZoom: 16,
          center: azavea,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        }

        var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

        var marker = new google.maps.Marker({
          position: azavea,
          map: map,
          title:"Address that Cicero geocoded!"
        });

        var districtoverlay = new google.maps.GroundOverlay(
            map_data_from_php.district_map_image_data.img_src,
            imageBounds);
        districtoverlay.setMap(map);
      }
    </script>
  </head>
  <body onload="initialize()">
    <div id="map_canvas" style="width:100%; height:100%"></div>
  </body>
</html>
