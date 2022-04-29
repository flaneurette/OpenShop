# Installing

OpenShop's installer will create a .htpasswd automatically, plus it restricts the admin by IP. However, it could be possible that there aren't enough rights to write a .htpasswd automatically due to server or security settings. If this is the case, then you could manually create the .htpasswd as per instructions below. To generate a secure password, use a webtool like: https://www.transip.nl/htpasswd/ 

# Password protected area.

It uses a .htaccess and Apache basic authentication to access the folder. It is restricted by IP.

A .htpasswd needs to be generated and placed below the /www/ folder. (However, be aware that this is not always possible due various server security settings.) By default, OpenShop writes it directly into the /administration/ folder for fail-safe reasons. The installer is not able to write below the /www/ folder, if you require better security, the .htpasswd needs to be created or moved manually.

Example: /home/path/.htpasswd

Example Value: 
```admin:$2a$13$LENS0vHxdMKBoD.wf2O9qel/R56e2f2n8.YApQcuFtf4/r.v.MGQS```

Which resolves to: 

 - Username: admin
 - Password: test

# Ip config

Add your IP:

Allow from 111.222.333.444


# Full example:

.htaccess
```
AuthType Basic
AuthName "OpenShop Administration"
AuthUserFile /var/users/admin/.htpasswd
Require valid-user
Order Deny,Allow
Deny from all
Allow from 111.222.333.444
```	
.htpasswd
```
admin:$2a$13$LENS0vHxdMKBoD.wf2O9qel/R56e2f2n8.YApQcuFtf4/r.v.MGQS
```

# Security
It would be wise to limit access to the panel by adding your private IP, to prevent any unwanted access. OpenShop relies on Apache authentication, and requires SSL to be enabled. Without SSL, OpenShop still works but is less secure. For more security, OpenShop also uses nonces in all forms in the administration panel to prevent CSRF. Most data is sanitized and checked. No warranty is given for any security issues arising through malicious files being uploaded by the admin itself, and the method to check this data is limited.

