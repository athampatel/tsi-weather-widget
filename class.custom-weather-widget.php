<?php
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
class CustomWeatherWidget
{
    protected $location;
    protected $apiKey;
    protected $name;
    /**
     * Implements __construct().
     *
     * CustomWeatherWidget constructor
     *
     * @return configure required actions
     */
    public function __construct()
    {
        $this->name = 'WeatherWidget';
        register_activation_hook(
            __FILE__, array(
            $this,
            'activationHook'
            )
        );

        register_deactivation_hook(
            __FILE__, array(
            $this,
            'deactivationHook'
            )
        );

        register_uninstall_hook(
            __FILE__, array(
            $this,
            'uninstallHook'
            )
        );

        add_action(
            'admin_menu', array(
            $this,
            'setupMenu'
            )
        );
        add_action(
            'wp_dashboard_setup', array(
            $this,
            'displayDashboardWidgets'
            )
        );
        add_action(
            'wp_ajax_nopriv_cws_widget', array(
            '$this',
            'loadWidgetInfo'
            )
        );
        add_action(
            'wp_ajax_cws_widget', array(
            $this,
            'loadWidgetInfo'
            )
        );
        add_action(
            'admin_enqueue_scripts', array(
            $this,
            'adminScritps'
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
    public static function adminScritps()
    {
        wp_enqueue_style('cws_widget_css', plugins_url('tsi-weather-widget/css/widget_style.css'), false, time());
        wp_enqueue_script('cws_widget_js', plugins_url('tsi-weather-widget/js/widget_script.js'), false, time());
        wp_localize_script(
            'cws_widget', 'cws_widgewt', array(
            'ajax_url' => admin_url('admin-ajax.php?action=cws_widget')
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
    public static function activationHook()
    {
        $value = serialize(array());
        if(!get_option(TSIWW_OPTIONKEY)) :
            add_option(TSIWW_OPTIONKEY, $value);
        endif;
    }
    /**
     * Implements deactivationHook().
     *
     * Clear active transient used to store weather information
     *
     * @return void
     */
    public static function deactivationHook()
    {
        if(!get_transient(TSIWW_INFOKEY)) :
            delete_transient(TSIWW_INFOKEY);
        endif;
    }
    /**
     * Implements uninstallHook().
     *
     * On deactivation delete the option.
     *
     * @return void
     */
    public static function uninstallHook()
    {
        if(get_option(TSIWW_OPTIONKEY)) :
            delete_option(TSIWW_OPTIONKEY);
        endif;
    }
    /**
     * Implements setupMenu().
     *
     * Plugin settings menu action add_options_page
     *
     * @return void
     */
    public static function setupMenu()
    {
        add_options_page(
            'Weather Widget', 'Weather Widget Settings', 'manage_options', 'tsi-weather-widget', array(
            'CustomWeatherWidget',
            'weatherWidgetSetup'
            )
        );
    }
    /**
     * Implements displayDashboardWidgets().
     *
     * Function Used to show the widget on admin dashbaord
     *
     * @return void
     */
    public static function displayDashboardWidgets()
    {
        global $wp_meta_boxes;
        wp_add_dashboard_widget(
            'custom_help_widget', 'Weather Widget', array(
            'CustomWeatherWidget',
            'weatherWidget'
            )
        );
    }

    /**
     * Implements getWeatherDetails().
     *
     * Function used to get detaild from the api.openweathermap.org API
     * 
     * @param boolean $fetch used to update the weather details on location changed, Default is false
     *   
     * @return void
     */
    public static function getWeatherDetails($fetch = 0)
    {
        $_options = get_option(TSIWW_OPTIONKEY);
        if ($_options != '') {
            $_options = unserialize($_options);
        }
        $apiKey = isset($_options['apiKey']) ? $_options['apiKey'] : 'd99cd2f7c1c88f1a21f2b2e90a2ec2d5';
        $lat = isset($_options['lat']) ? $_options['lat'] : '-33.7629';
        $lan = isset($_options['lan']) ? $_options['lan'] : '151.2707';
        $weather_info = '';

        //Retrive cached weather details to avaoid multiple API calls
        $weather_info = ($fetch) ? 0 : get_transient(TSIWW_INFOKEY);

        if ($apiKey != '' && $lat != '' && $lan != '' && !$weather_info) {
            $url = 'https://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lan . '&units=metric&appid=' . $apiKey;
            $api_response = wp_remote_get($url);
            $weather_info = wp_remote_retrieve_body($api_response);
            if (!empty($weather_info)) {
                $expire = TSIWW_EXPIRE * 60; // expire set to 45 minutes
                $add_cahce = set_transient(TSIWW_INFOKEY, $weather_info, $expire);
            }
        }
        if ($weather_info != '') {
              $weather_info = json_decode($weather_info, true);
        }

        return $weather_info;
    }
    /**
     * Implements weatherWidget().
     *
     * Function to show the weather information fetched and stored from API.
     *
     * @return void
     */
    public static function weatherWidget()
    {
        $info = CustomWeatherWidget::getWeatherDetails();
        $weather = isset($info['weather']) ? $info['weather'] : null;
        $main = isset($info['main']) ? $info['main'] : null;
        $wind = isset($info['wind']) ? $info['wind'] : null;
        $clouds = isset($info['clouds']) ? $info['clouds'] : null;        
        $_options = get_option(TSIWW_OPTIONKEY);
        if ($_options != '') {
            $_options = unserialize($_options);
        }
        $location = isset($_options['location']) ? $_options['location'] : $info['name'];
        if (!empty($weather)) { ?>
            <div class="weather-details">
            <?php
            foreach($weather as $key => $_item):
                ?>
                <div class="weather-item">
                    <div class="weather-info">
                        <div class="weather-location"><?php echo $location ?></div>
                            <div class="current-title"><?php echo '<strong>'.$_item['main'] .'</strong> <br>'. $_item['description'] ?></div>
                            <div class="weather-temp">
                                <span class="temp"><strong>Temp :</strong> <?php echo $main['temp'] ?> &#8451;</span>
                                <span class="feel-temp"><strong>Feels Like :</strong> <?php echo $main['feels_like'] ?> &#8451;</span>
                                <span class="temp"><strong>Min :</strong> <?php echo $main['temp_min'] ?> &#8451; - <strong>Max :</strong> <?php echo $main['temp_max'] ?> &#8451;</span>
                                <span class="humidity"><strong>Humidity :</strong> <?php echo $main['humidity'] ?>%</span>
                            </div>
                        </div>                                    
                        <div class="weather-icon"><img src="http://openweathermap.org/img/w/<?php echo $_item['icon'] ?>.png" /><a href="<?php echo admin_url('/options-general.php?page=tsi-weather-widget') ?>">Change location</a></div></div>
                </div>
                <?php
            endforeach;
        }

    }
    /**
     * Implements loadWidgetInfo().
     *
     * Get the location details from the ajax call
     *
     * @return void
     */
    public static function loadWidgetInfo()
    {
        $response = array(
            'status' => 0,
            'html' => '',
            'data' => '',
            'message' => '',
            'error' => 0
        );
        $_data = $_REQUEST;
        $method = $_data['method'];
        $_options = get_option(TSIWW_OPTIONKEY);
        if ($_options != '') {
            $_options = unserialize($_options);
        }
        $apiKey = isset($_options['apiKey']) ? $_options['apiKey'] : '';

        switch ($method)        {
        case 'fetch_location':
            $query = isset($_data['set_location']) ? sanitize_text_field($_data['set_location']) : 'Brookvale, NSW';
            $api = isset($_data['apiKey']) ? $_data['apiKey'] : $apiKey;
            $_url = 'http://api.openweathermap.org/geo/1.0/direct?q=' . $query . '&limit=20&appid=' . $api;
            $api_response = wp_remote_get($_url);
            $locations = json_decode(wp_remote_retrieve_body($api_response), true);


            $response['status'] = 1;
            if(!empty($locations)) :
                if(isset($locations['cod']) && $locations['cod'] == '401') :
                    $response['error'] = 1;
                    $response['message'] = $locations['message'];
                else:
                    $response['data'] = $locations;
                    $response['message'] = "Found " . count($locations) . ' items';
                endif;
            endif;
            $info = CustomWeatherWidget::getWeatherDetails(1);
            break;        
        default:
            break;
        }
        wp_send_json($response);
        wp_die();
    }
    /**
     * Implements weatherWidgetSetup().
     *
     * Plugins setting area to add API key and setup the location to fetch the details
     *
     * @return void
     */
    public static function weatherWidgetSetup()
    {
        if (isset($_REQUEST['tsiwigdget_setting'])) :
            update_option(TSIWW_OPTIONKEY, serialize($_REQUEST));
            CustomWeatherWidget::getWeatherDetails(1);
            echo '<script type="text/javascript">					
					window.location.reload();
				</script>';
        endif;
        $_options = get_option(TSIWW_OPTIONKEY);
        if ($_options != '') :
            $_options = unserialize($_options);
        endif;
        $apiKey = isset($_options['apiKey']) ? $_options['apiKey'] : '';
        $locationName = isset($_options['locationName']) ? $_options['locationName'] : '';
        $lat = isset($_options['lat']) ? $_options['lat'] : '';
        $lan = isset($_options['lan']) ? $_options['lan'] : ''; 
        $location = isset($_options['location']) ? $_options['location'] : ''; 
        ?> 

        <div class="metabox-holder setting-area">
            <div id="post-body">
                <div id="post-body-content">
                    <div class="postbox">
                        <h2>Weather Widget Settings</h2>
                        <form name="" action="" method="post">
                            <input type="hidden" name="aws-nonce-key" value="' . wp_create_nonce('tsi-weather-widget-hidden') . '" />
                            <div class="field-item label-flex type-text">
                                <div class="label"><strong>Enter API key</strong></div>
                                <div class="field">
                                    <input type="text" class="text-field apiKey" name="apiKey" value="<?php echo sanitize_text_field($apiKey) ?>" placeholder="Please enter the open weather map api key" />
                                    <small class="field-instruction">Get the <a href="https://openweathermap.org/api" target="_blank">Free API</a> from  Open Weather Map API</small>
                                </div>
                            </div>
                            <div class="field-item label-flex type-text">
                                <div class="label"><strong>Enter Location Details</strong></div>
                                <div class="field">
                                    <input type="text" class="text-field locationName" name="locationName" value="<?php echo sanitize_text_field($locationName) ?>"  placeholder="Type the location to see the Weather"/>
                                    <a href="javascript:void(0)" class="button secondary getlocation field-instruction">Fetch Location</a>
                                    <ul class="location-results"></ul>
                                    <input type="hidden" class="text-field val-lat" name="lat" value="<?php echo sanitize_text_field($lat) ?>" />
                                    <input type="hidden" class="text-field val-lan" name="lan" value="<?php echo sanitize_text_field($lan) ?>" />
                                    <input type="hidden" class="text-field val-name" name="location" value="<?php echo sanitize_text_field($location) ?>" />
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