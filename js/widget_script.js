jQuery(document).ready(function($){
	$(document.body).on('click','a.getlocation',function(e){		
		e.preventDefault();	
		var _api = $('input.apiKey').val();
		var locationName = $('input.locationName').val();
		if(_api.trim() == ''){	
			alert("Please enter the valid API key");
		}else if(_api.trim() == ''){	
			alert("Please enter the valid location");
		}else{
			var _obj = {set_location:locationName,method:'fetch_location',apiKey:_api,action:'cws_widgewt'};
			ajax_actions(_obj);
		}		
	});
	$(document.body).on('click','ul.location_results a.location_item',function(e){	
		$('input.locationName').val($(this).text());
		$('input.val_lat').val($(this).data('lat'));
		$('input.val_lan').val($(this).data('lon'));
	});
	
	ajax_actions = function(Object){ 	
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',		
			dataType:'json',
			data: Object,
			beforeSend: function( data ){

			},success: function( res ){
				if(res.status == 1 && Object.method == 'fetch_location'){
					 var locations = res.data;
					 var _html = '';
					 if(res.error == 1){
						_html = '<li><div class="notice notice-error">'+res.message+'</div></li>';
					 }else if(locations.length == 0){						 
						_html = '<li><div class="notice notice-error">Invalid location or city name</div></li>';
					 }else{
						 jQuery.each(locations,function(key,location){
							_html += '<li><a href="javascript:void(0)" class="location_item" data-lat="'+location.lat+'" data-lon="'+location.lon+'">'+location.name+', '+location.state+', '+location.country+'</a></li>';
						 });
					 }
					 if(_html != ''){
						$('ul.location_results').html(_html);
					 }
				}
			},complete:function(data){			
				 	
			}
		});
	};
});