# xm-labs-createusers

This is a data-source integration allowing for the automation of adding groups of users or single users via web-form

#Files
index.php
This is the file that loads in your webbrowser and displays a simple form (you can sdd your own css 

loading-1-1-.gif 
Loading image for form processing

doquery.php
Actual heart of the individual add. Be sure to update the appropriate usernames, passwords, and company names as well as xMatters API keys 

batchcreate.php
Performs CLI based batch addition of multiple users. Be sure to replace appropriate usernames, passwords, company names, and API keys

newxmattersusers.csv
file containig list of users to be added int he batch add. username, lastname, firstname, timezone

#How it works
GUI
User fills out the form and receives an email with instrucitons on how to add devices, subscribe to subscriptions etc. System checks to see if userid exists if it does not a new user is created if it does then an error is displayed.

CLI
Batch is loaded and each username is displayed as created or not created.

#Installation
put all files in a directory on a server that has access to your xMatters servers... if you intend to use the GUI then this should also have a webserver with appropriate php libaries etc.

#Testing
load the gui fillout the form. if you don't use a valid LDAP username the script will fail.

#Troubleshooting
If your LDAP is configured differently you may need to adjust how the authentication is completed going to the LDAP/ AD server
