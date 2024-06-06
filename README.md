Source deploy steps

(Prerequisite)

-Zabbix6.x.x/JAZ6.x.x database(Mysql)

-Zabbix6.x.x/JAZ6.x.x server/agent

-create hostgroup related with login user(zabbix)

-create host related with hostgroup(zabbix)

-create JOBNET related with host (JAZ)


1) Place the "bssapp" folder to /var/www/html/ of application server.

2) Edit the config /var/www/html/bssapp/conf/config.php
   #$server = "xx.xx.xx.xx"; (IP address of Zabbix/JAZ DB installed server)
  
  -if zabbix/jax application  server and DB server are separated
  #$api_url = "http://xx.xx.xx.xx/zabbix/api_jsonrpc.php"; (IP address of Zabbix/JAZ application server)
  
3) Restart http service
  #systemctm restart httpd

5) Access web url
   #http://xx.xx.xx.xx/bssapp/login.php (xx.xx.xx.xx -> IP address of source deploy server)
   
   User/Password is same as zabbix login user.
