# Am I Being Tracked?

Am I being tracked is a web-tool that looks for tracking values in HTTP headers. 

Mobile providers inject personalized values in the HTTP header to be able to track the users and the sites visited by the users. This headers are used for customer tracking. 

The web-tool will tell the user the current carrier and wether the user is or is not being tracked. 

**version** : 1.0

### Deployment :
on your webserver's home run :

**code**
```sh
git clone https://git.accessnow.org/Kevin/verizon.git
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
cd verizon
composer install
```
**database**
```sql
cd verizon/services
mysql -p
mysql> CREATE DATABASE IF NOT EXISTS `amibeingtracked` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
mysql> CREATE USER 'amibeingtracked'@'localhost' IDENTIFIED BY '$password';
mysql> GRANT ALL ON `amibeingtracked`.* TO 'amibeingtracked'@'localhost';
mysql> \q
mysql -p < amibeingtracked.sql
```
**configure**
```sh
cd verizon/services
cp config.yml.sample config.yml
editor config.yml
  #provide all needed informations  such the name of database that you just created and the db user...
```

### Developement & test envirement
Make sure to set the application to run into a developement envirement by editing the services/config.yml,
and setting the envirement to dev , devel or test.

Also configure the application to run on a test database always by editing the services/config.yml
file and setting the name of your test database after your create it on mysql (see database)

Test envirement comes with handy options that you can set from the url :
- Test any IP by hitting http://host/test.php?ip=MyIP
- Test the not-tracked page by hitting http://host/not-tracked.php
- Test costumized output on tracked page by hitting http://host/tracked.php?isp=SomeCarrier&perma=SomeHash
