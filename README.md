TLT 16 app
============
Registrations + Booking

```
$ npm install
$ composer install
$ bower install
$ php Lib/GoogleSheets/V4.php   # download prices data from google sheets
$ grunt

# CREATE USER 'tlt'@'localhost' IDENTIFIED BY 'password';
# GRANT ALL PRIVILEGES ON tlt.* TO 'tlt'@'localhost';
# FLUSH PRIVILEGES;

$ vim Config/database.php # edit db credentials
$ mysql -u user -p tlt < schema.sql
$ apache vhost is located at Config/Vhost/local.tlt.conf
$ chmod -R g+w tmp/{cache,logs} # webserver should be able to write there
```


enjoy!
