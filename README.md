MTM 18 app
============
Registrations + Lunches

```
$ npm install
$ composer install
$ bower install
$ php Lib/GoogleSheets/V4.php   # download prices data from google sheets
$ grunt

# CREATE USER 'mtm'@'localhost' IDENTIFIED BY 'password';
# GRANT ALL PRIVILEGES ON mtm.* TO 'mtm'@'localhost';
# FLUSH PRIVILEGES;

$ vim Config/database.php # edit db credentials
$ mysql -u user -p mtm < schema.sql
$ apache vhost is located at Config/Vhost/local.mtm.conf
$ chmod -R g+w tmp/{cache,logs} # webserver should be able to write there
```


enjoy!
