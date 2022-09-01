jQuery(document).ready(function($){
	$(document.body).on('click','a.getlocation',function(e){		
		e.preventDefault();	
		var html = '';
		var api = $('input.apiKey').val();
		var locationName = $('input.locationName').val();
		if(api.trim() == ''){				
			html = '<li><div class="notice notice-error">Please enter the valid API key</div></li>';			
			$('ul.location-results').html(html);
		}else if(api.trim() == ''){	
			html = '<li><div class="notice notice-error">Please enter the valid location</div></li>';			
			$('ul.location-results').html(html);
		}else{
			var _obj = {set_location:locationName,
				        method:'fetch_location',
						apiKey:api,
						action:'cws_widget'};
						
			ajax_actions(_obj);
		}		
	});
	$(document.body).on('click','ul.location-results a.location-item',function(e){	
		$('input.locationName').val($(this).text());
		$('input.val-lat').val($(this).data('lat'));
		$('input.val-lan').val($(this).data('lon'));
		$('input.val-name').val($(this).data('name'));
	});
	
	ajax_actions = function(Object){ 	
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',		
			dataType:'json',
			data: Object,
			beforeSend: function( data ){
				$('ul.location-results').html('');
			},success: function( res ){
				if(res.status == 1 && Object.method == 'fetch_location'){
					 var locations = res.data;
					 var responseString = '';
					 if(res.error == 1){
						responseString = '<li><div class="notice notice-error">' + res.message + '</div></li>';
					 }else if(locations.length == 0){						 
						responseString = '<li><div class="notice notice-error">Invalid location or city name</div></li>';
					 }else{
						 jQuery.each(locations,function(key,location){
							responseString += '<li><a href="javascript:void(0)" class="location-item" data-lat="'+location.lat+'" data-lon="' + location.lon + '" data-name="' + location.name+'">' + location.name + ', ' + location.state + ', ' + location.country + '</a></li>';
						 });
					 }
					 if(responseString != ''){
						$('ul.location-results').html(responseString);
					 }
				}
			},complete:function(data){			
				 	
			}
		});
	};
});