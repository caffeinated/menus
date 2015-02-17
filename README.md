Caffeinated Menu
================
Caffeinated Menu is originally based off of Lavary's [Laravel Menu](https://github.com/lavary/laravel-menu) package; rebuilt from the ground up (not necessarily a fork). This has a primary focus on Laravel 5 support with PSR coding standards. If you're looking for a menu builder solution for Laravel 4, please go check out [Laravel Menu](https://github.com/lavary/laravel-menu)!

Quick Installation
------------------
Begin by installing the package through Composer. Add `caffeinated/menu` to your composer.json file:

```
"caffeinated/menu": "~1.0@dev"
```

Then run `composer install` to pull the package in.

Once this operation is complete, simply add the service provider class and facade alias to your project's `config/app.php` file:

#### Service Provider
```php
'Caffeinated\Menu\MenuServiceProvider',
```

#### Facade
```
'Menu' => 'Caffeinated\Menu\Facades\Menu',
```

Usage
-----
Usage is simple. To create a menu simply call `Menu::make`, as follows:

```php
Menu::make('public', function($menu) {
	$menu->add('Home');
	$menu->add('About', 'about');
	$menu->add('Blog', 'blog');
	$menu->add('Contact', 'contact');
});
```

The best location to do this is to create a new service provider within your application (e.g. `MenuServiceProvider`).

Caffeinated Menu will automatically register the menu as a view composer (prepending your defined menu name with `menu_`) for all your views. Rendering your menu is simple:

```php
{!! $menu_public->asUl() !!}
```