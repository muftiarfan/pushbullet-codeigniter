# pushbullet-codeigniter

Pushbullet library for CodeIgniter, basically skeleton for actual PushBullet composer package.

# Requirements

  - Composer
  - https://github.com/ivkos/Pushbullet-for-PHP
  - PHP5 or above
  - Codeigniter
  - cURL library for PHP
  - Your Pushbullet access token: https://www.pushbullet.com/account

### Version
1.0

### Install

composer.json has already been setup, just run composer to get required packages.

- Autoload composer in codeignier
    
-  Add the following at top of your index.php in codeigniter.
```php
require_once __DIR__.'/vendor/autoload.php';
```
- Autoload the library from autoload.php
```php
$autoload['libraries'] = array('pushbullet');
```
- Add your api key in init() method of pushbullet.php
```php
$pb = new Pushbullet\Pushbullet('YOUR-API-KEY');
```
You are done.

# Usage

Adding notifications for queue to be pushed later, using process() or calling process() method using crontab.
```php
queue($title,$body,$url='',$type='link')
```
The $type is for keeping the queue type of the notification, for now the library is limited(not the actual one but mine) of doing only link, contact add and sending contacts note push.

Types of $type:
- link The library sends link to a channel you own on PushBullet
- add_contact it adds an email id to your contacts on pushbullet
- send_contact sends a particular email(contact) push notification as a note.
- If you would like to extend/imrpove this code-igniter library to more capibilities of actual composer package of https://github.com/ivkos/Pushbullet-for-PHP, check the functions at its Github.

# Limitations

There are certain limitations that occurred due to workarounds.

When using add_contact type the queue() method accepts email from $body and name from $title. (Yeah pretty stupid!)
and when using send_contact type the queue() method accepts email from $url 

