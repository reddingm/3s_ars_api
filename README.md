# 3s_ars_api



<VirtualHost arsrp.3-ships.com:80>
ServerName arsrp.3-ships.com
DocumentRoot /var/web/hosts/home_agentconnection/data-export
ScriptAlias /cgi-bin/ /var/web/hosts/cgi-bin
  ErrorLog logs/arsrp_3-ships_error_log
  CustomLog logs/arsrp_3-ships_access_log combined

 <Directory /var/web/hosts/home_agentconnection/data-export>
  Options Indexes FollowSymLinks
  AllowOverride all
 </Directory>
#RewriteEngine on
#RewriteCond %{SERVER_NAME} =hare.3-ships.com
#RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [L,NE,R=permanent]
</VirtualHost>