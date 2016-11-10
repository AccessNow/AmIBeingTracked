# Am I Being Tracked?

Am I Being Tracked is a web tool that looks for tracking values in HTTP headers injected by mobile carriers to track their users and the sites they visit.

The tool will tell the user their current carrier and whether the user is being tracked by this particular method. 

**Version**: 1.0

### Deployment
on your webserver run:

**Code**
```sh
git clone https://github.com/AccessNow/AmIBeingTracked.git
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
cd AmIBeingTracked
composer install
```
**Database**
```sql
cd AmIBeingTracked/services
mysql -p
mysql> CREATE DATABASE IF NOT EXISTS `amibeingtracked` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
mysql> CREATE USER 'amibeingtracked'@'localhost' IDENTIFIED BY '$password';
mysql> GRANT ALL ON `amibeingtracked`.* TO 'amibeingtracked'@'localhost';
mysql> \q
mysql -p < amibeingtracked.sql
```
**Configure**
```sh
cd AmIBeingTracked/services
cp config.yml.sample config.yml
editor config.yml
  #provide all needed information such the name of database that you just created and the db user...
```

### Development & test environment
Make sure to set the application to run in a developement environment by editing the services/config.yml,
and setting the environment to dev, devel or test.

Also configure the application to always run on a test database by editing the services/config.yml
file and setting the name of your test database after your create it on mysql (see database)

Test environment comes with handy options that you can set from the url:
- Test any IP by visiting http://host/test.php?ip=MyIP
- Test the not-tracked page by visiting http://host/not-tracked.php
- Test customized output on tracked page by visiting http://host/tracked.php?isp=SomeCarrier&perma=SomeHash
You can add headers using the GET -H or on browser by installing a header injector add-on.

### Workflow
Currently the tool is meant to be used as a website with the following workflow:

1. The file [test](test.php) contains all the necessary pieces of the tool to work.
2. It starts by looking up the Internet Service Provider of the user via a GeoIP service.
3. Then it looks within the headers of the HTTP request for a known tracking header value.
4. The final result is determined by the above lookups
5. The result are then saved into log files and a database
6. Depending of those results, the user is directed to an appropriate result page

Potential results include:

| Result                          | on Database Code  | Target page                        | Log File        |
| -----------------------------   | ----------------- | ---------------------------------- | --------------- |
| being tracked (new carrier)     | 2                 | [tracked.php](tracked.php)         | tracked.log     |
| being tracked                   | 1                 | [tracked.php](tracked.php)         | tracked.log     |
| not being tracked               | 0                 | [not-tracked.php](not-tracked.php) | not_tracked.log |
| not being tracked (new carrier) | -1                | [not-tracked.php](not-tracked.php) | not_tracked.log |
| no 4G or 3G were used           | -2                |  [no-lte.php](no-lte.php)          |  others.log     |

### Customization 
#### Lookup services
The tool uses MaxMind GeoIP insight service, this serice can be replaced by your favorite lookup provider or a database. To do so, you can change the value of the geoprovider type on the [config file](services/config.yml).

#### Result pages
The result pages are mainly HTML, and content can be added, removed at will.

#### Assets
The tool comes with some [assets](img), feel free to use/edit/share them.
