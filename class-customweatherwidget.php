<?php
/**
 * Custom Weather Widget Class file
 *
 * @file
 * @category  Class
 * Custom Weather Widget Class
 * @package   TSIWeatherWidget
 * @author    Tender Software <info@tendersoftware.in>
 * @copyright 2022 Tender Software
 * @license   GPL-2.0+
 * @link      https://tendersoftware.com/
 */

/**
 * CustomWeatherWidget Class Doc Comment
 *
 * Custom Weather Widget to retrieve application wide
 * URLs based on active webinstance.
 *
 * @category  Class
 * @package   CustomWeatherWidget
 * @author    Tender Software <info@tendersoftware.in>
 * @copyright 2022 Tender Software, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE
 * @link      https://tendersoftware.com/
 *
 * @since 1.0.1
 */
class CustomWeatherWidget {

	/**
	 * Implements __construct().
	 *
	 * CustomWeatherWidget constructor
	 */
	public function __construct() {
		register_activation_hook(
			__FILE__,
			array(
				$this,
				'activation_hook',
			)
		);

		register_deactivation_hook(
			__FILE__,
			array(
				$this,
				'deactivation_hook',
			)
		);

		register_uninstall_hook(
			__FILE__,
			array(
				$this,
				'uninstall_hook',
			)
		);

		add_action(
			'admin_menu',
			array(
				$this,
				'setup_menu',
			)
		);
		add_action(
			'wp_dashboard_setup',
			array(
				$this,
				'display_dashboard_widgets',
			)
		);
		add_action(
			'wp_ajax_nopriv_cws_widget',
			array(
				'$this',
				'load_widget_nfo',
			)
		);
		add_action(
			'wp_ajax_cws_widget',
			array(
				$this,
				'load_widget_nfo',
			)
		);
		add_action(
			'admin_enqueue_scripts',
			array(
				$this,
				'admin_scritps',
			)
		);
	}
	/**
	 * Implements admin_scritps().
	 *
	 * Used to include required CSS and JS.
	 *
	 * @return void
	 */
	public static function admin_scritps() {
		wp_enqueue_style( 'cws_widget_css', plugins_url( 'tsi-weather-widget/css/widget_style.css' ), false, time() );
		wp_enqueue_script( 'cws_widget_js', plugins_url( 'tsi-weather-widget/js/widget_script.js' ), false, time(), true );
		wp_localize_script(
			'cws_widget_js',
			'cws_widget',
			array(
				'ajax_url'  => admin_url( 'admin-ajax.php?action=cws_widget' ),
				'tsi_nonce' => wp_create_nonce( 'tsi-nonce' ),
			)
		);

	}
	/**
	 * Implements activationHook().
	 *
	 * On plugin activation set option value as null.
	 *
	 * @return void
	 */
	public static function activation_hook() {
		$value = wp_json_encode( array() );
		if ( ! get_option( TSIWW_OPTIONKEY ) ) :
			add_option( TSIWW_OPTIONKEY, $value );
		endif;
	}
	/**
	 * Implements deactivationHook().
	 *
	 * Clear active transient used to store weather information
	 *
	 * @return void
	 */
	public static function deactivation_hook() {
		if ( get_transient( TSIWW_INFOKEY ) ) :
			delete_transient( TSIWW_INFOKEY );
		endif;
	}
	/**
	 * Implements uninstallHook().
	 *
	 * On deactivation delete the option.
	 *
	 * @return void
	 */
	public static function uninstall_hook() {
		if ( get_option( TSIWW_OPTIONKEY ) ) :
			delete_option( TSIWW_OPTIONKEY );
		endif;
		if ( get_option( TSIWW_INFOKEY ) ) :
			delete_option( TSIWW_INFOKEY );
		endif;		
	}
	/**
	 * Implements setupMenu().
	 *
	 * Plugin settings menu action add_options_page
	 *
	 * @return void
	 */
	public static function setup_menu() {
		add_options_page(
			'Weather Widget',
			'Weather Widget Settings',
			'manage_options',
			'tsi-weather-widget',
			array(
				'CustomWeatherWidget',
				'weather_widget_setup',
			)
		);
	}
	/**
	 * Implements display_dashboard_widgets().
	 *
	 * Function Used to show the widget on admin dashbaord
	 *
	 * @return void
	 */
	public static function display_dashboard_widgets() {
		global $wp_meta_boxes;
		wp_add_dashboard_widget(
			'custom_help_widget',
			'Weather Widget',
			array(
				'CustomWeatherWidget',
				'weather_widget',
			)
		);
	}
	/**
	 * Implements get_weather_details().
	 *
	 * Function used to get detaild from the api.openweathermap.org API
	 *
	 * @param boolean $fetch used to update the weather details on location changed, Default is false.
	 *
	 * @return Array
	 */
	public static function get_weather_details( $fetch = 0 ) {
		$_options = get_option( TSIWW_OPTIONKEY );
		if ( '' !== $_options ) :
			$_options = json_decode( $_options, true );
		endif;
		$api_key      = isset( $_options['api_key'] ) ? $_options['api_key'] : 'd99cd2f7c1c88f1a21f2b2e90a2ec2d5';
		$lat          = isset( $_options['lat'] ) ? $_options['lat'] : '-33.7629';
		$lan          = isset( $_options['lan'] ) ? $_options['lan'] : '151.2707';
		$weather_info = '';
		// Retrive cached weather details to avaoid multiple API calls.
		$weather_info = ( $fetch ) ? 0 : get_transient( TSIWW_INFOKEY );
		if ( '' !== $api_key && '' !== $lat && '' !== $lan && ! $weather_info ) :
			$url          = 'https://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lan . '&units=metric&appid=' . $api_key;
			$api_response = wp_remote_get( $url );
			$weather_info = wp_remote_retrieve_body( $api_response );
			if ( ! empty( $weather_info ) ) :
				$expire    = TSIWW_EXPIRE * 60; // expire set to 45 minutes.
				$add_cahce = set_transient( TSIWW_INFOKEY, $weather_info, $expire );
			endif;
		endif;
		if ( '' !== $weather_info ) :
			$weather_info = json_decode( $weather_info, true );
		endif;

		return $weather_info;
	}
	/**
	 * Implements weatherWidget().
	 *
	 * Function to show the weather information fetched and stored from API.
	 *
	 * @return void
	 */
	public static function weather_widget() {
		$info     = self::get_weather_details();
		$weather  = isset( $info['weather'] ) ? $info['weather'] : null;
		$main     = isset( $info['main'] ) ? $info['main'] : null;
		$_options = get_option( TSIWW_OPTIONKEY );
		if ( '' !== $_options ) :
			$_options = json_decode( $_options, true );
		endif;
		$location = isset( $_options['location'] ) ? $_options['location'] : $info['name'];
		if ( ! empty( $weather ) ) : ?>
			<div class="weather-details">
			<?php
			foreach ( $weather as $key => $_item ) :
				?>
				<div class="weather-item">
					<div class="weather-info">
						<div class="weather-location"><?php echo esc_html( $location ); ?></div>
							<div class="current-title"><?php echo '<strong>' . esc_html( $_item['main'] ) . '</strong> <br>' . esc_html( $_item['description'] ); ?></div>
							<div class="weather-temp"> 
								<span class="temp"><strong>Temp :</strong> <?php echo esc_html( $main['temp'] ); ?> &#8451;</span>
								<span class="feel-temp"><strong>Feels Like :</strong> <?php echo esc_html( $main['feels_like'] ); ?> &#8451;</span>
								<span class="temp"><strong>Min :</strong> <?php echo esc_html( $main['temp_min'] ); ?> &#8451; - <strong>Max :</strong> <?php echo esc_html( $main['temp_max'] ); ?> &#8451;</span>
								<span class="humidity"><strong>Humidity :</strong> <?php echo esc_html( $main['humidity'] ); ?>%</span>
							</div>
						</div>                                    
						<div class="weather-icon"><img src="http://openweathermap.org/img/w/<?php echo esc_html( $_item['icon'] ); ?>.png" /><a href="<?php echo esc_html( admin_url( '/options-general.php?page=tsi-weather-widget' ) ); ?>">Change location</a></div></div>
				</div>
				<?php
			endforeach;
		endif;

	}
	/**
	 * Implements load_widget_nfo().
	 *
	 * Get the location details from the ajax call
	 *
	 * @return void
	 */
	public static function load_widget_nfo() {
		$response  = array(
			'status'  => 0,
			'html'    => '',
			'data'    => '',
			'message' => '',
			'error'   => 0,
		);
		$tsi_nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';

		if ( wp_verify_nonce( $tsi_nonce, 'tsi-nonce' ) ) {

			$_data    = $_REQUEST;
			$method   = $_data['method'];
			$_options = get_option( TSIWW_OPTIONKEY );
			if ( '' !== $_options ) :
				$_options = json_decode( $_options, true );
			endif;
			$api_key = isset( $_options['api_key'] ) ? $_options['api_key'] : '';

			switch ( $method ) :
				case 'fetch_location':
					$query              = isset( $_data['set_location'] ) ? sanitize_text_field( $_data['set_location'] ) : 'Brookvale, NSW';
					$api                = isset( $_data['api_key'] ) ? $_data['api_key'] : $api_key;
					$_url               = 'http://api.openweathermap.org/geo/1.0/direct?q=' . $query . '&limit=20&appid=' . $api;
					$api_response       = wp_remote_get( $_url );
					$locations          = json_decode( wp_remote_retrieve_body( $api_response ), true );
					$response['status'] = 1;
					if ( ! empty( $locations ) ) :
						if ( isset( $locations['cod'] ) && '401' === $locations['cod'] ) :
							$response['error']   = 1;
							$response['message'] = $locations['message'];
						else :
							$response['data']    = $locations;
							$response['message'] = 'Found ' . count( $locations ) . ' items';
						endif;
					endif;
					$info = self::get_weather_details( 1 );
					break;
				default:
					break;
			endswitch;
		}
		wp_send_json( $response );
		wp_die();
	}
	/**
	 * Implements weatherWidgetSetup().
	 *
	 * Plugins setting area to add API key and setup the location to fetch the details
	 *
	 * @return void
	 */
	public static function weather_widget_setup() {

		if ( isset( $_REQUEST['tsiwigdget_setting'] ) ) :
			$status    = 0;
			$tsi_nonce = isset( $_REQUEST['tsiww_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['tsiww_nonce'] ) ) : '';
			if ( wp_verify_nonce( $tsi_nonce, 'tsi_widget' ) ) :
				update_option( TSIWW_OPTIONKEY, wp_json_encode( $_REQUEST ) );
				self::get_weather_details( 1 );
				$status = 1;
			endif;
			echo '<script type="text/javascript">					
					window.loocation.href = ' . esc_html( admin_url( 'admin-ajax.php?action=tsi-weather-widget&status=' . $status ) ) . '
				</script>';
		endif;
		$_options = get_option( TSIWW_OPTIONKEY );
		if ( '' !== $_options ) {
			$_options = json_decode( $_options, true );
		}
		$api_key       = isset( $_options['api_key'] ) ? $_options['api_key'] : '';
		$location_name = isset( $_options['locationName'] ) ? $_options['locationName'] : '';
		$lat           = isset( $_options['lat'] ) ? $_options['lat'] : '';
		$lan           = isset( $_options['lan'] ) ? $_options['lan'] : '';
		$location      = isset( $_options['location'] ) ? $_options['location'] : '';
		?>
		<div class="metabox-holder setting-area">
			<div id="post-body">
				<div id="post-body-content">
					<div class="postbox">
						<h2>Weather Widget Settings</h2>
						<form name="" action="" method="post">
							<input type="hidden" name="tsiww_nonce" value="<?php echo esc_html( wp_create_nonce( 'tsi_widget' ) ); ?>" />
							<div class="field-item label-flex type-text">
								<div class="label"><strong>Enter API key</strong></div>
								<div class="field">
									<input type="text" class="text-field api_key" name="api_key" value="<?php echo esc_html( $api_key ); ?>" placeholder="Please enter the open weather map api key" />
									<small class="field-instruction">Get the <a href="https://openweathermap.org/api" target="_blank">Free API</a> from  Open Weather Map API</small>
								</div>
							</div>
							<div class="field-item label-flex type-text">
								<div class="label"><strong>Enter Location Details</strong></div>
								<div class="field">
									<input type="text" class="text-field locationName" name="locationName" value="<?php echo esc_html( $location_name ); ?>"  placeholder="Type the location to see the Weather"/>
									<a href="javascript:void(0)" class="button secondary getlocation field-instruction">Fetch Location</a>
									<ul class="location-results"></ul>
									<input type="hidden" class="text-field val-lat" name="lat" value="<?php echo esc_html( $lat ); ?>" />
									<input type="hidden" class="text-field val-lan" name="lan" value="<?php echo esc_html( $lan ); ?>" />
									<input type="hidden" class="text-field val-name" name="location" value="<?php echo esc_html( $location ); ?>" />
								</div>
							</div>
							<div class="field-item">
								<input type="submit" class="button is-primary" name="tsiwigdget_setting" value="Save Settings" />
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
