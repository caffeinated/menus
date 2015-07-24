Caffeinated Menus
=================
[![Laravel 5.0](https://img.shields.io/badge/Laravel-5.0-orange.svg?style=flat-square)](http://laravel.com)
[![Laravel 5.1](https://img.shields.io/badge/Laravel-5.1-orange.svg?style=flat-square)](http://laravel.com)
[![Source](http://img.shields.io/badge/source-caffeinated/menus-blue.svg?style=flat-square)](https://github.com/caffeinated/menus)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

Easily create dynamic menus from within your Laravel 5 application.

Caffeinated Menus is originally based off of Lavary's [Laravel Menu](https://github.com/lavary/laravel-menu) package; rebuilt from the ground up (not necessarily a fork). This has a primary focus on Laravel 5 support with PSR coding standards. If you're looking for a menu builder solution for Laravel 4, please go check out [Laravel Menu](https://github.com/lavary/laravel-menu)!

The package follows the FIG standards PSR-1, PSR-2, and PSR-4 to ensure a high level of interoperability between shared PHP code. At the moment the package is not unit tested, but is planned to be covered later down the road.

Documentation
-------------
You will find user friendly and updated documentation in the wiki here: [Caffeinated Menus Wiki](https://github.com/caffeinated/menus/wiki)

Quick Installation
------------------
Begin by installing the package through Composer. Depending on what version of Laravel you are using (5.0 or 5.1), you'll want to pull in the `~1.0` or `~2.0` release, respectively:

#### Laravel 5.0.x
```
composer require caffeinated/menus=~1.0
```

#### Laravel 5.1.x
```
composer require caffeinated/menus=~2.0
```

Once this operation is complete, simply add the service provider class and facade alias to your project's `config/app.php` file:

#### Laravel 5.0.x
##### Service Provider
```php
'Caffeinated\Menus\MenusServiceProvider',
```

##### Facade
```php
'Menu' => 'Caffeinated\Menus\Facades\Menu',
```

#### Laravel 5.1.x
##### Service Provider
```php
Caffeinated\Menus\MenusServiceProvider::class,
```

##### Facade
```php
'Menu' => Caffeinated\Menus\Facades\Menu::class,
```

And that's it! With your coffee in reach, start building out some awesome menus!
