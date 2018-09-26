<?php
namespace Caffeinated\Menus;

use BadMethodCallException;
use Illuminate\Support\Str;

class Builder
{
    /**
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    protected $groupStack = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $reserved = ['route', 'action', 'url', 'prefix', 'parent', 'secure', 'raw'];

    /**
     * @var int
     */
    protected $lastId;

    /**
     * Create a new Builder instance.
     *
     * @param string  $name
     * @param array   $config
     */
    public function __construct($name, $config)
    {
        $this->name   = $name;
        $this->config = $config;
        $this->items  = new Collection;
    }

    /**
     * Add an item to the defined menu.
     *
     * @param  string       $title
     * @param  array|string $options
     *
     * @return Item
     */
    public function add($title, $options = '')
    {
        $item = new Item($this, $this->id(), $title, $options);

        $this->items->push($item);

        $this->lastId = $item->id;

        return $item;
    }

    /**
     * Generate a unique ID for every item added to the menu.
     *
     * @return int
     */
    protected function id()
    {
        return $this->lastId + 1;
    }

    /**
     * Extract the valid attributes from the passed options.
     *
     * @param array $options
     *
     * @return array
     */
    public function extractAttributes($options = array())
    {
        if (is_array($options)) {
            if (count($this->groupStack) > 0) {
                $options = $this->mergeWithLastGroup($options);
            }

            return array_except($options, $this->reserved);
        }

        return array();
    }

    /**
     * Insert a divider after the item.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function divide($attributes = array())
    {
        $attributes['class'] = self::formatGroupClass(['class' => 'divider'], $attributes);

        $this->items->last()->divider = $attributes;
    }

    /**
     * Return the configuration value by key.
     *
     * @param string $key
     *
     * @return string
     */
    public function config($key)
    {
        return $this->config[$key];
    }

    /**
     * Get the prefix from the last group of the stack.
     *
     * @return mixed
     */
    public function getLastGroupPrefix()
    {
        if (count($this->groupStack) > 0) {
            return array_get(last($this->groupStack), 'prefix', '');
        }

        return null;
    }

    /**
     * Format the groups class.
     *
     * @return mixed
     */
    public static function formatGroupClass($new, $old)
    {
        if (isset($new['class'])) {
            $classes = trim(trim(array_get($old, 'class')).' '.trim(array_get($new, 'class')));

            return implode(' ', array_unique(explode(' ', $classes)));
        }

        return array_get($old, 'class');
    }

    /*
    |--------------------------------------------------------------------------
    | Fetching Methods
    |--------------------------------------------------------------------------
    |
    */

    /**
     * Fetches and returns all menu items.
     *
     * @return Collection
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Returns all items with no parents.
     *
     * @return Collection
     */
    public function roots()
    {
        return $this->whereParent();
    }

    /**
     * Fetches and returns a menu item by it's slug.
     *
     * @param string $slug
     *
     * @return Item
     */
    public function get($slug)
    {
        return $this->whereSlug($slug)->first();
    }

    /**
     * Facade method for the get() method.
     *
     * @param string $slug
     *
     * @return Item
     */
    public function item($slug)
    {
        return $this->get($slug);
    }

    /**
     * Fetches and returns a menu item by it's ID.
     *
     * @param integer $id
     *
     * @return Item
     */
    public function find($id)
    {
        return $this->whereId($id)->first();
    }

    /**
     * Fetches and returns the first menu item.
     *
     * @return Item
     */
    public function first()
    {
        return $this->items->first();
    }

    /**
     * Fetches and returns the last menu item.
     *
     * @return Item
     */
    public function last()
    {
        return $this->items->last();
    }

    /**
     * Fetches and returns all active state menu items.
     *
     * @return Collection
     */
    public function active()
    {
        $activeItems = array();

        foreach ($this->items as $item) {
            if ($item->data('active')) {
                $activeItems[] = $item;
            }
        }

        return $activeItems;
    }

    /*
    |--------------------------------------------------------------------------
    | Dispatch Methods
    |--------------------------------------------------------------------------
    |
    */

    /**
     * Get the action type from the options.
     *
     * @param array $options
     *
     * @return string
     */
    public function dispatch($options)
    {
        if (isset($options['url'])) {
            return $this->getUrl($options);
        } elseif (isset($options['route'])) {
            return $this->getRoute($options['route']);
        } elseif (isset($options['action'])) {
            return $this->getAction($options['action']);
        }

        return null;
    }

    /**
     * Get the action for a "url" option.
     *
     * @param array|string $options
     *
     * @return string
     */
    protected function getUrl($options)
    {
        foreach ($options as $key => $value) {
            $$key = $value;
        }

        $secure = (isset($options['secure']) and $options['secure'] === true) ? true : false;

        if ($prefix) {
            $prefix = $prefix.'/';
        }

        if (is_array($url)) {
            if (self::isAbsolute($url[0])) {
                return $url[0];
            }

            return url()->to($prefix.$url[0], array_slice($url, 1), $secure);
        }

        if (self::isAbsolute($url)) {
            return $url;
        }

        return url()->to($prefix.$url, array(), $secure);
    }

    /**
     * Get the route action for a "route" option.
     *
     * @param array|string $route
     *
     * @return string
     */
    protected function getRoute($route)
    {
        if (is_array($route)) {
            return url()->route($route[0], array_slice($route, 1));
        }

        return url()->route($route);
    }

    /**
     * Get the controller action for a "action" option.
     *
     * @param array|string $action
     *
     * @return string
     */
    protected function getAction($action)
    {
        if (is_array($action)) {
            return url()->action($action[0], array_slice($action, 1));
        }

        return url()->action($action);
    }

    /**
     * Determines if the given URL is absolute.
     *
     * @param string $url
     *
     * @return bool
     */
    public static function isAbsolute($url)
    {
        return parse_url($url, PHP_URL_SCHEME) or false;
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Methods
    |--------------------------------------------------------------------------
    |
    */

    /**
     * Filter menu items through a callback.
     *
     * Since menu items are stored as a collection, this will
     * simply forward the callback to the Laravel Collection
     * filter() method and return the results.
     *
     * @param callable $callback
     *
     * @return Builder
     */
    public function filter($callback)
    {
        if (is_callable($callback)) {
            $this->items = $this->items->filter($callback);
        }

        return $this;
    }

    /**
     * Filter menu items recursively.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return Collection
     */
    public function filterRecursively($attribute, $value)
    {
        $collection = new Collection;

        $this->items->each(function ($item) use ($attribute, $value, &$collection) {
            if (! property_exists($item, $attribute)) {
                return false;
            }

            if ($item->$attribute == $value) {
                $collection->push($item);

                if ($item->hasChildren()) {
                    $collection = $collection->merge($this->filterRecursively($attribute, $item->id));
                }
            }
        });

        return $collection;
    }

    /**
     * Sorts the menu based on key given in ascending order.
     *
     * @param string $key
     *
     * @return Builder
     */
    public function sortBy($key)
    {
        $this->items = $this->items->sortBy(function ($item) use ($key) {
            return $item->$key;
        });

        return $this;
    }

    /**
     * Sorts the menu based on key given in descending order.
     *
     * @param string $key
     *
     * @return Builder
     */
    public function sortByDesc($key)
    {
        $this->items = $this->items->sortByDesc(function ($item) use ($key) {
            return $item->$key;
        });

        return $this;
    }

    /**
     * Filter menu items based on Shinobi permissions.
     *
     * @return Builder
     */
    public function guard()
    {
        if (class_exists('Caffeinated\Shinobi\Shinobi')) {
            $this->filter(function ($item) {
                if (! $item->data('can') and ! $item->data('canatleast')) {
                    return true;
                } elseif ($item->data('canatleast')) {
                    return \Shinobi::canAtLeast($item->data('canatleast'));
                } else {
                    return \Shinobi::can($item->data('can'));
                }
            });
        }

        return $this;
    }

    /**
     * Dynamic search method against a menu attribute.
     *
     * @param string $method
     * @param array  $args
     *
     * @return Item|bool
     */
    public function __call($method, $args)
    {
        preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $method, $matches);

        if ($matches) {
            $attribute = Str::lower($matches[1]);
        } else {
            throw new BadMethodCallException('Call to undefined method '.$method);
        }

        $value     = $args ? $args[0] : null;
        $recursive = isset($args[1]) ? $args[1] : false;

        if ($recursive) {
            return $this->filterRecursively($attribute, $value);
        }

        return $this->items->filter(function ($item) use ($attribute, $value) {
            if (isset($item->data[$attribute]) && $item->data[$attribute] == $value) {
                return true;
            }

            if (! property_exists($item, $attribute)) {
                return false;
            }

            if ($item->$attribute == $value) {
                return true;
            }

            return false;
        })->values();
    }

    /**
     * Returns menu item by name.
     *
     * @param string $property
     *
     * @return Item
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return $this->whereSlug($property)->first();
    }
}
