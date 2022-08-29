
Technical decisions made
==========================================================
We used to store the Settings option and results details into set_transient and get_transient methods instead of wp_cache objects. Limited expiry time to 45 mins as mentioned



Description:
==============
WordPress admin dashboard Weather Widget Plugins uses https://api.openweathermap.org API to fetch the location and the weather details. The Plugins menu can be found in the WordPress Settings Menu tab where the location can be changed.

	1. To fetch the Geo information of the city or location, entered /geo/1.0/direct 
	2. To fetch the Weather information of selected location /data/2.5/weather	
	
By default, the plugins show the weather location of Brookvale, New South Wales, AU.

Openweathermap TEST API KEY: d99cd2f7c1c88f1a21f2b2e90a2ec2d5

Plugins Screen
==============
	1. Plugin Setting screen.
		https://prnt.sc/_74RPCBby2Wc
		
	2. Admin Dashboard Widget Screen
		https://prnt.sc/efcqzMqPC2le	
	

Time to Complete
=================
It takes about 4 hours.


Demo
=====
https://tender.net/demo/wp-admin/index.php

Username: admin_rcc7mcwn
Password : Jnh~WjyR21tn_05!