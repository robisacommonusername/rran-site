# RRAN Website

Code for RRAN website.

## Requirements
 - Apache 2
 - PHP 5.4 or greater, with intl extension. mbcrypt not required, but highly desireable.
 - Mysql 5 or greater (also works on postgresql >= 9)

## Installation
 - Upload all code to server, and set the document root to the webroot folder.
 - Make sure that the folders tmp/ and webroot/uploads (and all of their subfolders) are writeable by the webserver.
 - Set the database connection details in config/app.php
 - Via ssh, run `./bin/cake migrations migrate`
 - Browse to http://yourdomain/install, and follow the instructions to create the admin user
