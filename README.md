Alt-H1
======
WordPress admin dashboard Weather Widget Plugins is uses https://api.openweathermap.org  API to fetch the Location and the Weather details.
Plugins menu can be found in WordPress Settings Menu tab were the location can be changed.


A brief justification for any key technical decisions made
==========================================================

We used to store the Settings option and results details into set_transient and get_transient methods instead of wp_cache obejcts and time limit it set to 45mins.




Description:
==============
WordPress admin dashboard Weather Widget Plugins is uses https://api.openweathermap.org following API

	1. To fetch the Geo information of the city or location entered /geo/1.0/direct 
	2. To fetch the Weather information of selected location /data/2.5/weather	
	
By Default the plugins show the Weather location of Brookvale, New South Wales, AU


Plugins Screen
==============
	1. Plugin Setting screen.
		https://prnt.sc/_74RPCBby2Wc
		
	2. Admin Dashboard Widget Screen
		https://prnt.sc/efcqzMqPC2le	
	

Time to Complete
=================
it takes about 3 Hours and 45 Mins