Caffeinated Menus
=================
Caffeinated Menus is originally based off of Lavary's [Laravel Menu](https://github.com/lavary/laravel-menu) package; rebuilt from the ground up (not necessarily a fork). This has a primary focus on Laravel 5 support with PSR coding standards. If you're looking for a menu builder solution for Laravel 4, please go check out [Laravel Menu](https://github.com/lavary/laravel-menu)!

Quick Installation
------------------
Begin by installing the package through Composer. Add `caffeinated/menus` to your composer.json file:

```
"caffeinated/menus": "~1.0@dev"
```

Then run `composer install` to pull the package in.

Once this operation is complete, simply add the service provider class and facade alias to your project's `config/app.php` file:

#### Service Provider
```php
'Caffeinated\Menus\MenusServiceProvider',
```

#### Facade
```
'Menu' => 'Caffeinated\Menus\Facades\Menu',
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

More documentation coming soon
------------------------------

### Example Bootstrap menu template

#### Twig

**navbar.twig.php**
```php
<nav class="navbar navbar-inverse" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			<a href="#" class="navbar-brand">Caffeinated Menu</a>
		</div>

		<div class="collapse navbar-collapse" id="menu-collapse">
			<ul class="nav navbar-nav">
				{{ include('partials.menu.items', {'items': menu_public.roots()}) }}
			</ul>
		</div>
	</div>
</nav>
```

**items.twig.php**
```php
{% for item in items %}
	<li {{ (item.hasChildren()) ? 'class="dropdown"' : null }}>
		<a href="{{ item.url() }}" {{ (item.hasChildren()) ? 'class="dropdown-toggle" data-toggle="dropdown"' : null }}>
			{{ item.title }} {{ (item.hasChildren()) ? '<b class="caret"></b>' : null }}
		</a>

		{% if (item.hasChildren()) %}
			<ul class="dropdown-menu">
				{% for child in item.children() %}
					<li><a href="{{ child.url() }}">{{ child.title }}</a></li>
				{% endfor %}
			</ul>
		{% endif %}
	</li>
{% endfor %}
```

#### Blade

**navbar.blade.php**
```php
<nav class="navbar navbar-inverse" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#menu-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>

			<a href="#" class="navbar-brand">Caffeinated Menu</a>
		</div>

		<div class="collapse navbar-collapse" id="menu-collapse">
			<ul class="nav navbar-nav">
				@include('partials.menu.items', ['items'=> $menu_public->roots()])
			</ul>
		</div>
	</div>
</nav>
```

**items.blade.php**
```php
@foreach($items as $item)
	<li @if($item->hasChildren())class ="dropdown"@endif>
		@if($item->link) <a @if($item->hasChildren()) class="dropdown-toggle" data-toggle="dropdown" @endif href="{{ $item->url() }}">
			{{ $item->title }}
			@if($item->hasChildren()) <b class="caret"></b> @endif
		</a>
		@else
			{{$item->title}}
		@endif
		@if($item->hasChildren())
			<ul class="dropdown-menu">
				@foreach($item->children() as $child)
					<li><a href="{{ $child->url() }}">{{ $child.title }}</a></li>
				@endforeach
			</ul>
		@endif
	</li>
	@if($item->divider)
		<li{{\HTML::attributes($item->divider)}}></li>
	@endif
@endforeach
```