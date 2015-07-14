1. Extract to your LibreNMS plugins directory html/plugins so you should see something like /opt/librenms/html/plugins/Weathermap/
  The best way to do this is via git. Go to your install directory and then html/plugins.
  Enter:
    git clone https://github.com/librenms-plugins/Weathermap.git
2. Make the configs directory writeable by your web server, either chown apache:apache configs/ or chmod 777 configs.
  I'd highly advise you choose the first option, replace apache:apache with your web servers user and group.
3. Enable the plugin from the LibreNMS Plugins -> Plugin Admin menu.
4. Create your maps, please note when you create a MAP, please click Map Style, ensure Overlib is selected for HTML Style and click submit.
5. Also, ensure you set an output image filename and output HTML filename in Map Properties.
  I'd recommend you use the output folder as this is excluded from git updates (i.e enter output/mymap.png and output/mymap.html).
6. Enable the cron process by editing your current LibreNMS cron file (typically /etc/cron.d/librenms) and add the following:
  LibreNMS:
    */5 * * * * root /opt/librenms/html/plugins/Weathermap/map-poller.php >> /dev/null 2>&1
7. If you need to, set $basehref within map-poller.php if your install is in another directory than standard.
