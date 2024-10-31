<?php

$api_key = Realty_Portal::get_setting( 'nearby_places', 'yelp_api_key', '' );
define( 'API_KEY', $api_key );
define( 'API_HOST', "https://api.yelp.com" );
define( 'SEARCH_PATH', "/v3/businesses/search" );
define( 'BUSINESS_PATH', "/v3/businesses/" );

function rp_addons_nearby_places_yelp_nearby( $property ) {

	$yelp_on = Realty_Portal::get_setting( 'nearby_places', 'yelp_on', false );

	$latitude  = get_post_meta( $property->ID, 'latitude', true );
	$longitude = get_post_meta( $property->ID, 'longitude', true );
	if ( empty( $latitude ) || empty( $longitude ) ) {
		return false;
	}

	$yelp_cll = $latitude . ',' . $longitude;

	$yelp_categories = array(
		'active'             => array(
			'name' => esc_html__( 'Active Life', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-bicycle',
		),
		'arts'               => array(
			'name' => esc_html__( 'Arts & Entertainment', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-picture-o',
		),
		'auto'               => array(
			'name' => esc_html__( 'Automotive', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-car',
		),
		'beautysvc'          => array(
			'name' => esc_html__( 'Beauty & Spas', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-cutlery',
		),
		'education'          => array(
			'name' => esc_html__( 'Education', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-graduation-cap',
		),
		'eventservices'      => array(
			'name' => esc_html__( 'Event Planning & Services', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-birthday-cake',
		),
		'financialservices'  => array(
			'name' => esc_html__( 'Financial Services', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-money',
		),
		'food'               => array(
			'name' => esc_html__( 'Food', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-shopping-basket',
		),
		'health'             => array(
			'name' => esc_html__( 'Health & Medical', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-medkit',
		),
		'homeservices'       => array(
			'name' => esc_html__( 'Home Services', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-wrench',
		),
		'hotelstravel'       => array(
			'name' => esc_html__( 'Hotels & Travel', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-bed',
		),
		'localflavor'        => array(
			'name' => esc_html__( 'Local Flavor', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-coffee',
		),
		'localservices'      => array(
			'name' => esc_html__( 'Local Services', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-dot-circle-o',
		),
		'massmedia'          => array(
			'name' => esc_html__( 'Mass Media', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-television',
		),
		'nightlife'          => array(
			'name' => esc_html__( 'Nightlife', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-glass',
		),
		'pets'               => array(
			'name' => esc_html__( 'Pets', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-paw',
		),
		'professional'       => array(
			'name' => esc_html__( 'Professional Services', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-suitcase',
		),
		'publicservicesgovt' => array(
			'name' => esc_html__( 'Public Services & Government', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-university',
		),
		'realestate'         => array(
			'name' => esc_html__( 'Real Estate', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-building-o',
		),
		'religiousorgs'      => array(
			'name' => esc_html__( 'Religious Organizations', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-universal-access',
		),
		'restaurants'        => array(
			'name' => esc_html__( 'Restaurants', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-cutlery',
		),
		'shopping'           => array(
			'name' => esc_html__( 'Shopping', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-shopping-bag',
		),
		'transport'          => array(
			'name' => esc_html__( 'Transportation', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-bus',
		),
		'trainstations'      => array(
			'name' => esc_html__( 'Train Stations', 'realty-portal-nearby-places' ),
			'icon' => 'fa fa-train',
		),
	);

	if ( $yelp_on == 1 ) {

		$yelp_term = Realty_Portal::get_setting( 'nearby_places', 'yelp_term', array( 'multiple' => array( 'realestate' ) ) );
		$limit     = Realty_Portal::get_setting( 'nearby_places', 'yelp_limit', '' );
		$unit      = Realty_Portal::get_setting( 'nearby_places', 'yelp_unit', 'mile' );

		if ( empty( API_KEY ) || empty( $yelp_term ) ) {
			return false;
		}


		/**
		 * Show Yelp Nearby Places
		 */
		?>
        <div class="rp-property-yelp">

            <div class="rp-yelp-title">
                <h3 class="rp-title-box"><?php echo esc_html__( 'What\'s Nearby?', 'realty-portal-nearby-places' ); ?></h3>
                <div class="yelp-logo">
					<?php echo esc_html__( "powered by", "realty-portal-nearby-places" ); ?>
                    <img src="<?php echo RP_ADDON_NEARBY_PLACES_ASSETS; ?>images/yelp-logo.png" alt="yelp"
                         class="v-align-bottom">
                </div>
            </div>

			<?php
			if ( ! empty( $yelp_term['multiple'] ) ) {
				foreach ( $yelp_term['multiple'] as $value ) {

					$term_id   = $value;
					$term_name = $yelp_categories[ $term_id ]['name'];
					$term_icon = $yelp_categories[ $term_id ]['icon'];

					$current_lat = '';
					$current_lng = '';
					$result      = rp_yelp_search( $term_id, $yelp_cll, $limit );
					$result      = json_decode( $result, true );

					if ( ! isset( $result['error'] ) ):

						$businesses = $result['businesses'];
						if ( sizeof( $businesses ) != 0 ):
							?>
                            <div class="yelp-cat-item">

                                <h4 class="cat-title">
                                    <span class="yelp-cat-icon"><i class="<?php echo $term_icon; ?>"></i></span>
									<?php echo $term_name; ?>
                                </h4>
								<?php rp_addons_nearby_places_yelp_nearby_term( $businesses, $current_lat, $current_lng, $unit ); ?>
                            </div>
						<?php endif; ?>
					<?php endif; ?>
					<?php
				}
			}
			?>
        </div><!-- /.rp-property-yelp -->
		<?php
	}
}

add_action( 'rp_after_single_property_summary', 'rp_addons_nearby_places_yelp_nearby', 100, 1 );

function rp_yelp_request( $host, $path, $url_params = array() ) {
	// Send Yelp API Call
	try {
		$curl = curl_init();
		if ( false === $curl ) {
			throw new Exception( 'Failed to initialize' );
		}
		$url = $host . $path . "?" . http_build_query( $url_params );
		curl_setopt_array( $curl, array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,  // Capture response.
			CURLOPT_ENCODING       => "",  // Accept gzip/deflate/whatever.
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "GET",
			CURLOPT_SSL_VERIFYPEER  => false,	
			CURLOPT_HTTPHEADER     => array(
				"authorization: Bearer " . API_KEY,
				"cache-control: no-cache",
			),
		) );
		$response = curl_exec( $curl );
		if ( false === $response ) {
			throw new Exception( curl_error( $curl ), curl_errno( $curl ) );
		}
		$http_status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
		if ( 200 != $http_status ) {
			throw new Exception( $response, $http_status );
		}
		curl_close( $curl );
	} catch ( Exception $e ) {

	}

	return $response;
}

function rp_yelp_search( $term, $location, $limit ) {
	$url_params = array();

	$url_params['term']     = $term;
	$url_params['location'] = $location;
	$url_params['limit']    = $limit;

	return rp_yelp_request( API_HOST, SEARCH_PATH, $url_params );
}

/**
 * Get Reponse
 */
if ( ! function_exists( 'rp_addons_yelp_widget_curl' ) ) {
	function rp_addons_yelp_widget_curl( $signed_url ) {

		// Send Yelp API Call using WP's HTTP API
		$data = wp_remote_get( $signed_url );

		//Use curl only if necessary
		if ( empty( $data['body'] ) ) {

			$ch = curl_init( $signed_url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			$data = curl_exec( $ch ); // Yelp response
			curl_close( $ch );
			$data     = noo_yelp_update_http_for_ssl( $data );
			$response = json_decode( $data );
		} else {

			$data     = noo_yelp_update_http_for_ssl( $data );
			$response = json_decode( $data['body'] );
		}

		// Handle Yelp response data
		return $response;
	}
}

/**
 * Function update http for SSL
 *
 */
if ( ! function_exists( 'noo_yelp_update_http_for_ssl' ) ) {
	function noo_yelp_update_http_for_ssl( $data ) {

		if ( ! empty( $data['body'] ) && is_ssl() ) {
			$data['body'] = str_replace( 'http:', 'https:', $data['body'] );
		} elseif ( is_ssl() ) {
			$data = str_replace( 'http:', 'https:', $data );
		}
		$data = str_replace( 'http:', 'https:', $data );

		return $data;
	}
}

/**
 * Get Yelp Nearby HTML
 */

function rp_addons_nearby_places_yelp_nearby_term( $businesses, $current_lat, $current_lng, $unit ) {
	$yelp_term_img = Realty_Portal::get_setting( 'nearby_places', 'yelp_term_img', 1 );
	echo '<ul class="yelp-result-list">';
	foreach ( $businesses as $data ) {
		if ( empty( $data ) || isset( $data['error'] ) ) {

			echo '<li><p>' . esc_html__( 'API unavailable in this location.', 'realty-portal-nearby-places' ) . '</p></li>';
			continue;
		}

		$item_id = $data['id'];

		$location_distance = '';

		if ( isset( $data['coordinate'] ) ) :

			$location_lat      = $data['coordinate']['latitude'];
			$location_lng      = $data['coordinate']['longitude'];
			$theta             = $current_lng - $location_lng;
			$dist              = sin( deg2rad( $current_lat ) ) * sin( deg2rad( $location_lat ) ) + cos( deg2rad( $current_lat ) ) * cos( deg2rad( $location_lat ) ) * cos( deg2rad( $theta ) );
			$dist              = acos( $dist );
			$dist              = rad2deg( $dist );
			$miles             = $dist * 60 * 1.1515;
			$location_distance = '<span class="time-review"> (' . round( $miles, 2 ) . esc_html__( 'mi', 'realty-portal-nearby-places' ) . ') </span>';

			if ( $unit == 'kilo' ) {
				$miles             = $miles * 1.6093;
				$location_distance = '<span class="time-review"> (' . round( $miles, 2 ) . esc_html__( 'km', 'realty-portal-nearby-places' ) . ') </span>';
			}

		endif;

		$avatar = 'https://s3-media3.fl.yelpcdn.com/assets/srv0/yelp_styleguide/fe8c0c8725d3/assets/img/default_avatars/business_90_square.png';

		if ( ! empty( $data['image_url'] ) ) {
			$avatar = $data['image_url'];
		}

		if ( ! isset( $data['name'] ) ) {
			continue;
		}

		?>
        <li id="yelp-item-<?php echo $item_id; ?>">
            <div class="yelp-cat-detail rp-row">

				<?php if ( $yelp_term_img == 1 ): ?>

                    <div class="avatar">
                        <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $data['name'] ); ?>">
                    </div>

				<?php endif; ?>

                <div class="story">

                    <h5><?php echo $data['name']; ?><?php echo $location_distance; ?></h5>

                    <div class="review">
						<?php if ( isset( $data['review_count'] ) ): ?>
                           
                           <div class="rp-stars-rating">
                           	<?php $rating = $data['rating'] * 20;?>
                           	<span style="width: <?php echo absint( $rating ); ?>%"></span>
                           </div>
                           
                           <span class="time-review"> <?php echo esc_html( $data['review_count'] ) . esc_html__( ' reviews', 'realty-portal-nearby-places' ); ?></span>
						<?php endif; ?>
            		</div>

					<?php if ( isset( $data['location'] ) && isset( $data['location']['display_address'] ) ): ?>
                        <address>
							<?php echo esc_html__( implode( ', ', $data['location']['display_address'] ) ); ?>
                        </address>
					<?php endif; ?>

                </div>
            </div>
        </li>
		<?php
	}
	echo '</ul>';
}

if ( ! function_exists( 'rp_addons_nearby_places_walkscore' ) ) :

	function rp_addons_nearby_places_walkscore( $property ) {

		$walkscore_on = Realty_Portal::get_setting( 'nearby_places', 'walkscore_on', false );

		if ( $walkscore_on == 1 ) {

			$walkscore_api_key = Realty_Portal::get_setting( 'nearby_places', 'walkscore_api_key', '' );

			if ( $walkscore_api_key == '' ) {
				return;
			}

			$lat     = get_post_meta( $property->ID, 'latitude', true );
			$long    = get_post_meta( $property->ID, 'longitude', true );
			$address = get_post_meta( $property->ID, 'address', true );
			$address = stripslashes( $address );
			$address = urlencode( $address );

			$url = "http://api.walkscore.com/score?format=json&address=$address";
			$url .= "&lat=$lat&lon=$long&wsapikey=$walkscore_api_key";

			$response = wp_remote_get( $url, array( 'timeout' => 120 ) );

			if ( is_array( $response ) ) {

				$body      = wp_remote_retrieve_body( $response ); // use the content
				$walkscore = json_decode( $body ); // json decode

				if ( ! property_exists( $walkscore, 'walkscore' ) ) {
					return;
				}

				?>
                <div class="rp-property-walkscore">
                    <div class="rp-walkscore-title">
                        <h3 class="rp-title-box"><?php echo esc_html__( 'Walkscore', 'realty-portal-nearby-places' ); ?></h3>
                    </div>
                    <div class="walkscore_details">
                        <img src="https://cdn.walk.sc/images/api-logo.png" alt="walkscore"/>
                        <span>
							<?php echo esc_html( $walkscore->walkscore ); ?>
                            / <?php echo esc_html( $walkscore->description ); ?>
                            <a href="<?php echo esc_url( $walkscore->ws_link ); ?>" target="_blank">
					            <?php echo esc_html__( 'more details here', 'realty-portal-nearby-places' ); ?>
				            </a>
				        </span>
                    </div>
                </div>
				<?php
			}
		}
	}

	add_action( 'rp_after_single_property_summary', 'rp_addons_nearby_places_walkscore', 105, 1 );

endif;