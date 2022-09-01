<?php
/**
 *  * TSI Weather Widget
 
 * @category  Wordpress-plugin
 * @package   TSIWeatherWidget
 * @author    Tender Software <info@tendersoftware.in>
 * @copyright 2022 Tender Software
 * @license   GPL-2.0+ 
 * @link      https://tendersoftware.com/
 * 
 * @wordpress-plugin
 * Plugin Name: TSI Weather Widget
 * Plugin URI: https://tendersoftware.com/
 * Description: Display Weather Details for selected City or Town.
 * Author: Tender Software
 * Version: 1.0.0
 * Author URI: https://tendersoftware.com/
 */
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/
define('TSIWW_OPTIONKEY', 'tsiwigdget_options');
define('TSIWW_INFOKEY', 'tsi_weather_info');
define('TSIWW_EXPIRE', 45);
require_once 'class.custom-weather-widget.php';
$WeatherWidget = new CustomWeatherWidget();