Steggy 28 Aug 2014


##temp1.php

Using the Beaglebone Black as a temperature monitor and recorder

Including temp1d daemon

Display/Record temperature and also the local web for adjusting the .ini file without having to ssh to the unit

The code directory should be set up as a user other than root

Soft links are created to the runtime and the web page share the .ini file tempset.ini

The server side is templogweb

#Setup

Required:

PHP

PHP-Cli 

Apache


Root will not follow symlinks - this is the reason for the soft links

temp1.php is run through temp1d as root

create the directory bin in a users directory

git clone the templog into this directory

Add this user to www-data group /etc/group

create link in the root/bin directory ln -s /home/[user]/bin/templog/ templog

create link in /var/www ln -s /home/[user]/bin/templog/webtemp/ webtemp


#USE

I run temp1d from /etc/init.d  - please read the top of temp1.php to make sure directories are correct

to view the local status go to http://[bbb ip]/webtemp/webtemp.php 
