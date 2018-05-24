# isblocked

Based on [workerman](https://github.com/walkor/Workerman) public microservice to check is IP blocked by RKN.

Checkout [isitblocked.tk](http://isitblocked.tk)

Require PECL [Ev](https://pecl.php.net/package/ev) extension installed for perfomance reasons.

# Installation

Clone this repo and install dependencies through composer, in the folder with `composer.json` file run:
```composer install```

On FreeBSD:

```
sudo pkg install php72-pecl-ev
```

On MacOS:

```
pecl install ev
```
If you have PHP installed through brew you may need create a symlink on `ev.so`.
Example:
```ln -s /usr/local/Cellar/php72/7.2.4/pecl/20170718/ev.so /usr/local/Cellar/php/7.2.4/lib/php/20170718/ev.so```

Start: `php server.php start -d`
