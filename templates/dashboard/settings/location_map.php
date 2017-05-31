<!-- added by kas5986 for shop locations ..3/30/2017 -->
<?php 
/**
 * kas5986 - shop location modify...
 * Google map api - not getting any idea where to put this to show on both dashboard 
 * and shop page you can move this to  right place for now just to test is it working 
 */
wp_enqueue_script( 'google_map', 'https://maps.googleapis.com/maps/api/js?key='.WC_Vendors::$pv_options->get_option('vendor_shop_google_api').'&libraries=places&callback=initMap', array('jquery') );
?>


<div class="pv_shop_location_container">
	<p><b><?php _e( 'Shop Location', 'wcvendors' ); ?></b><br/>
		<?php _e( 'Physical Location for your Shop.', 'wcvendors' ); ?><br/>

		<input type="text" name="pv_shop_location" id="pv_shop_location" placeholder="Latitude,Longitude"
			   value="<?php echo get_user_meta( $user_id, 'pv_shop_location', true ); ?>"/>
	</p>
	
    <div class="pac-card" id="pac-card">
      <div>
        <div id="title">
          <?php _e( 'Autocomplete search', 'wcvendors' ); ?>
        </div>
        <div id="type-selector" class="pac-controls">
          <input type="radio" name="type" id="changetype-all" checked="checked">
          <label for="changetype-all">All</label>

          <input type="radio" name="type" id="changetype-establishment">
          <label for="changetype-establishment">Establishments</label>

          <input type="radio" name="type" id="changetype-address">
          <label for="changetype-address">Addresses</label>

          <input type="radio" name="type" id="changetype-geocode">
          <label for="changetype-geocode">Geocodes</label>
        </div>
        <div id="strict-bounds-selector" class="pac-controls">
          <input type="checkbox" id="use-strict-bounds" value="">
          <label for="use-strict-bounds">Strict Bounds</label>
        </div>
      </div>
      <div id="pac-container">
        <input id="pac-input" type="text"
            placeholder="Enter a location">
      </div>
    </div>
    <div id="map" style="height: 400px; widht: 100%;"></div>

</div>
    <script>
    <!--

      function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: {lat: -33.8688, lng: 151.2195},
          zoom: 13
        });
        var card = document.getElementById('pac-card');
        var input = document.getElementById('pac-input');
        var types = document.getElementById('type-selector');
        var strictBounds = document.getElementById('strict-bounds-selector');

        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(card);

        var autocomplete = new google.maps.places.Autocomplete(input);

        // Bind the map's bounds (viewport) property to the autocomplete object,
        // so that the autocomplete requests use the current map bounds for the
        // bounds option in the request.
        autocomplete.bindTo('bounds', map);

        var marker = new google.maps.Marker({
          map: map,
  		draggable: true,
          anchorPoint: new google.maps.Point(0, -29)
        });
        // drag response
        google.maps.event.addListener(marker, 'dragend', function(e) {
         // alert(this.getPosition());
        	displayPosition(this.getPosition());
        });
        // click response
        google.maps.event.addListener(marker, 'click', function(e) {
          //alert(this.getPosition());
        	displayPosition(this.getPosition());
        });

        autocomplete.addListener('place_changed', function() {
          
          marker.setVisible(false);
          var place = autocomplete.getPlace();
          if (!place.geometry) {
            // User entered the name of a Place that was not suggested and
            // pressed the Enter key, or the Place Details request failed.
            window.alert("No details available for input: '" + place.name + "'");
            return;
          }

          // If the place has a geometry, then present it on a map.
          if (place.geometry.viewport) {
            map.fitBounds(place.geometry.viewport);
          } else {
            map.setCenter(place.geometry.location);
            map.setZoom(17);  // Why 17? Because it looks good.
          }
          marker.setPosition(place.geometry.location);
          marker.setVisible(true);

          var address = '';
          if (place.address_components) {
            address = [
              (place.address_components[0] && place.address_components[0].short_name || ''),
              (place.address_components[1] && place.address_components[1].short_name || ''),
              (place.address_components[2] && place.address_components[2].short_name || '')
            ].join(' ');
          }


        });

        // Sets a listener on a radio button to change the filter type on Places
        // Autocomplete.
        function setupClickListener(id, types) {
          var radioButton = document.getElementById(id);
          radioButton.addEventListener('click', function() {
            autocomplete.setTypes(types);
          });
        }

        setupClickListener('changetype-all', []);
        setupClickListener('changetype-address', ['address']);
        setupClickListener('changetype-establishment', ['establishment']);
        setupClickListener('changetype-geocode', ['geocode']);

        document.getElementById('use-strict-bounds')
            .addEventListener('click', function() {
              console.log('Checkbox clicked! New state=' + this.checked);
              autocomplete.setOptions({strictBounds: this.checked});
            });

        // displays a position on two <input> elements
        function displayPosition(pos) {
          document.getElementById('pv_shop_location').value = pos.lat() + ',' + pos.lng();
        }

        
      }
    -->
</script>
