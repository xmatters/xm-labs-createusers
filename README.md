# xm-labs-createusers

This is a data-source integration allowing for the automation of adding groups of users or single users via php web-form

# Files
* [index.php](index.php) - This is the file that loads in your webbrowser and displays a simple form (you can add your own css )
* [loading-1-1.gif](loading-1-1.gif) - Loading image for form processing
* [doquery.php](doquery.php) - Actual heart of the individual add. Be sure to update the appropriate usernames, passwords, and company names as well as xMatters API keys 
* [batchcreate.php](batchcreate.php) - Performs CLI based batch addition of multiple users. Be sure to replace appropriate usernames, passwords, company names, and API keys
* [newxmattersusers.csv](newxmattersusers.csv) - file containing a list of users to be added in the batch add. username, lastname, firstname, timezone

# How it works
*GUI*
User fills out the form and receives an email with instrucitons on how to add devices, subscribe to subscriptions etc. System checks to see if userid exists if it does not a new user is created if it does then an error is displayed.

*CLI*
Batch is loaded and each username is displayed as created or not created.

When the script runs successfully either from the single user GUI or from the batch script the individual users will receive the following email: (it needs to be customized for each company )

**NOTE: The Users email address is pulled from the LDAP server configureation.**

**NOTE: I BCC myself on the email in order to track who was created when. I have logs but its nice to have the email in case they ask for them again.**

![Successful Email](/media/createuser_xmatters_email_sent.png "Example of sent email")

# Prerequisites
This script requires that PHP Version 5.3.3 at least (Has not been tested with older PHP versions) There is nothing which has been depreciated so should work in with PHP 7 without issue.
You can get more information about PHP at [php.net](http://php.net/) and specifically about PHP on CENTOS 6 at: [RedHat.com](https://www.redhat.com/en/search/PHP) 

# Installation
Copy all files to a directory on a server with PHP and has access to your xMatters servers. If you intend to use the GUI then this should also have a webserver with appropriate php libaries etc.

Then, edit the `doquery.php` file, lines 48-51 to update the LDAP credentials:

```php
  // Connect to the AD server
  $host = "ad.yourco.com";
  $dn = "ou=yourco,dc=yourco,dc=com";
  $user = "YOURCO\\".$uid;

```


# Who Can Use This
The GUI can be used by anyone who can reach the webpage (so if you don't want everyone in your org using it don't put it somewhere people can get to.) At Windstream we use this to create Standard USers so they can subscribe to outages and other notifications without having to bother an administrator.

In order to utilize the CLI you have to be able to ssh to the server where the CLI exists and have suitable permissions to run the script.  **NOTE: The SCRIPT MUST HAVE an Administrator level user configured in order to work properly**  

# Testing
Load the gui and fill out the form. if you don't use a valid LDAP username the script will fail.
![Form Appearance](/media/create_xmatters_user.png "Basic Form")

The batch file runs from the BLI as such
php batchcreate.php
![Batch CLI Example](/media/batchcreate_xmatters_users_cli.png "CLI Example")

and in this case it failed because the users exist:
![Batch CLI Errors Example](/media/batchcreate_xMatters_users_errors.png "CLI Users Exist")
Here is the GUI failure for an existing user:
![Form With Error](/media/Create_xMatters_User_error.png "User Exists")

# Troubleshooting
If your LDAP is configured differently you may need to adjust how the authentication is completed going to the LDAP/ AD server

