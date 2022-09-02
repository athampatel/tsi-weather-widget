/**
 * * Widget JavaScript
 *
 * @category  JavaScript
 * @package   TSIWeatherWidget
 * @author    Tender Software <info@tendersoftware.in>
 * @copyright 2022 Tender Software
 * @license   GPL-2.0+
 * @link      https://tendersoftware.com/
 */

 jQuery( document ).ready( function( $ ) {
	$( document.body ).on( 'click' , 'a.getlocation' , function( e ) {
		e.preventDefault();
		var html         = '';
		var api          = $( 'input.api_key' ).val();
		var locationName = $( 'input.locationName' ).val();
		var tsiww_nonce  = cws_widget.tsi_nonce;
		if ( api.trim() === '' ) {
			html = '<li><div class="notice notice-error">Please enter the valid API key</div></li>';
			$( 'ul.location-results' ).html( html );
		} else if ( locationName.trim() == '' ) {
			html = '<li><div class="notice notice-error">Please enter the valid location</div></li>';
			$( 'ul.location-results' ).html( html );
		} else {
			var _obj = {set_location:locationName,
				method:'fetch_location',
				api_key:api,
				nonce:tsiww_nonce,
				action:'cws_widget'
			};
			ajax_actions( _obj );
		}
	});
	$( document.body ).on( 'click' , 'ul.location-results a.location-item' , function( e ) {
		$( 'input.locationName' ).val( $( this ).text() );
		$( 'input.val-lat' ).val( $( this ).data( 'lat' ) );
		$( 'input.val-lan' ).val( $( this ).data( 'lon' ) );
		$( 'input.val-name' ).val( $( this ).data( 'name' ) );
	});
	ajax_actions = function( Object ){
		$.ajax({
			url: cws_widget.ajax_url,
			type: 'POST',
			dataType:'json',
			data: Object,
			beforeSend: function( data ){
				$( 'ul.location-results' ).html( '' );
			},success: function( res ){
				if ( res.status === 1 && Object.method === 'fetch_location' ) {
					var locations      = res.data;
					var responseString = '';
					if ( res.error === 1 ) {
						responseString = '<li><div class="notice notice-error">' + res.message + '</div></li>';
					} else if ( locations.length === 0 ) {
						responseString = '<li><div class="notice notice-error">Invalid location or city name</div></li>';
					} else {
						jQuery.each( locations, function( key, location ) {
							responseString += '<li><a href="#" class="location-item" data-lat="' + location.lat + '" data-lon="' + location.lon + '" data-name="' + location.name + '">' + location.name + ', ' + location.state + ', ' + location.country + '</a></li>';
						});
					}
					if ( responseString !== '' ) {
						$( 'ul.location-results' ).html( responseString );
					}
				}
			},complete:function( data ){

			}
		});
	};
}
);
