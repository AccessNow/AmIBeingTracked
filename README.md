# Am I Being Tracked?

Am I being tracked is a web-tool that looks for tracking values in HTTP headers. 

Mobile providers inject personalized values in the HTTP header to be able to track the users and the sites visited by the users. This headers are used for customer tracking. 

The web-tool will tell the user the current carrier and wether the user is or is not being tracked. 

**version** : 1.0

### Deployment :
on your webserver's home run :

**code**
```sh
git clone https://git.accessnow.org/bechir/aibt.git
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
You can add headers using the GET -H or on browser by installing a header injector add-on.

### Workflow
The Current tool is meant at the first place to be used as a website.
1. The file [test](test.php) contains all the necessairy peices of the tool to work.
2. It start by looking up the Internet Service Provider of the user via a GeoIP service.
3. Then it look within the headers of the http request for a known tracking header value.
4. The final result is determined by the aboves lookups
5. The result are then saved into log files and a database
6. depending of those results the user is directed to a result page.

The results can be either :
| Result | on Database Code | Target page | Log File |
| ------- | ----------------- | ------------ | --------- |
| being tracked | 1 | [tracked.php](tracked.php) | tracked.log |
| not being tracked | 0 | [not-tracked.php](not-tracked.php) | not_tracked.log |
| no lte 4G or 3G were used | -1 | [no-lte.php](no-lte.php) | others.log |

in case a new is appear the test return 2 if the user is bieng tracked otherwise it return -2.

### Customization 
#### lookup services :
The tool uses MaxMind GeoIP insight service, this serice can be replaced by your favorit lookup provider or a database.

To do so you can change the value of the geoprovider type on the [config file](services/config.yml), depending on it add any needed value that are being fetched.

#### Result pages :
The result pages are mainly HTML and content can be added, removed at will.

#### Assets :
The tool comes with some [assets](img), feel free to use/edit/share them.