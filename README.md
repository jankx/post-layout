# post-layout
WordPress post and custom post type layouts


# Usage
To use Jankx post layout you must have a [Template engine](https://github.com/jankx/template-engine) before create post layout instance.

## Create template Engine
Please choose your favorite template Engine. in this example I was use Plates engine

```
use Jankx\TemplateEngine\Engine\Plates;

$engine = Plates::create('your_engine_id');

// Set up directory include templates
$engine->setDefaultTemplateDir('full_path_to_default_templates_directory');
$engine->setDirectoryInTheme('directory_name_in_your_theme');

// Setup template engine Environment
$engine->setupEnvironment();
```

## Create post layout instance.

Please create post layout instance before call `init` hook to ensure all features is working correctly.

```
use Jankx\PostLayout\PostLayoutManager;

$postLayoutManager = PostLayoutManager::createInstance($engine);
```

## Create a post layout

Note `$wp_query` is a instance of [WP_Query](https://developer.wordpress.org/reference/classes/wp_query/) of WordPress core. If `$wp_query` is not set, Post Layout will be use global `$wp_query` variable.

```
$cardLayout = $postLayoutManager->createLayout('card', $wp_query);

// Show post layout content to end user.
$cardLayout->render();
```


# Layout supports
- Card
- Grid
- List
- Carousel
- Tabs
