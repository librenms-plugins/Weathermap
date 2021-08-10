### Notice
This is a modification of the PHP Network Weathermap plugin for libreNMS, the idea is to migrate to version 0.98a of the original Network Weathermap

----
### Prerequisites

Network-WeatherMap requires php pear to work.

### Installing Network-WeatherMap

### Step 1. 
Extract to your LibreNMS plugins directory `/opt/librenms/html/plugins` so you should see something like `/opt/librenms/html/plugins/Weathermap/`
The best way to do this is via git. Go to your install directory and then `/opt/librenms/html/plugins`
Enter:
#### Original repository
    `git clone https://github.com/librenms-plugins/Weathermap.git`
    
### Step 2.
Inside the html/plugins directory, change the ownership of the Weathermap directory by typing `chown -R librenms:librenms Weathermap/`
Make the configs directory writeable `chmod 775 /opt/librenms/html/plugins/Weathermap/configs`
Note if you are using SELinux you need to input the following command `chcon -R -t httpd_cache_t Weathermap/`
#### (Note: In order to make the config writable for set ownership of the config directory to `chown -R www-data:www-data /opt/librenms/html/plugins/Weathermap/configs/`  )

### Step 3. 
Enable the cron process by editing your current LibreNMS cron file (typically /etc/cron.d/librenms) and add the following:
LibreNMS:  `*/5 * * * * librenms /opt/librenms/html/plugins/Weathermap/map-poller.php >> /dev/null 2>&1`

### Step 4. 
Enable the plugin from LibreNMS Web UI in OverView ->Plugins -> Plugin Admin menu.

### Step 5. 
Now you should see Weathermap Overview -> Plugins -> Weathermap
Create your maps, please note when you create a MAP, please click Map Style, ensure Overlib is selected for HTML Style and click submit.
Also, ensure you set an output image filename and output HTML filename in Map Properties.
I'd recommend you use the output folder as this is excluded from git updates (i.e enter output/mymap.png and output/mymap.html).

Optional: If your install is in another directory than standard, set `$basehref` within `map-poller.php`.
