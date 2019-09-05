Caffeinated Menus
=================
[![Source](http://img.shields.io/badge/source-caffeinated/menus-blue.svg?style=flat-square)](https://github.com/caffeinated/menus)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

---

Easily create dynamic menus from within your Laravel 5 application.

Caffeinated Menus is originally based off of Lavary's [Laravel Menu](https://github.com/lavary/laravel-menu) package with support for the Caffeinated Shinobi package. For a more robust and complete package, please go check out [Laravel Menu](https://github.com/lavary/laravel-menu)!

The package follows the FIG standards PSR-1, PSR-2, and PSR-4 to ensure a high level of interoperability between shared PHP code. At the moment the package is not unit tested, but is planned to be covered later down the road.

Documentation
-------------
You will find user friendly and updated documentation in the wiki here: [Caffeinated Menus Wiki](https://github.com/caffeinated/menus/wiki)

Quick Installation
------------------
Begin by installing the package through Composer.

```
composer require caffeinated/menus
```

Once this operation is complete, simply add the service provider class and facade alias to your project's `config/app.php` file:

#### Service Provider
```php
Caffeinated\Menus\MenusServiceProvider::class,
```

#### Facade
```php
'Menu' => Caffeinated\Menus\Facades\Menu::class,
```

And that's it! With your coffee in reach, start building out some awesome menus!
