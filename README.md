Hype
====

Hype provides all the tools needed to build any kind of template system.

It provides an infrastructure to load template files and optionally monitor them
for changes. It also provides a concrete template engine implementation using
PHP with additional tools for escaping and separating templates into blocks and
layouts, in a non-blocking manner.

Getting Started
---------------

```
$ composer require azjezz/hype
```

```php
use Hype\Loader\FilesystemLoader;
use Hype\PhpEngine;
use Hype\Helper\SlotsHelper;
use Hype\TemplateNameParser;

$filesystemLoader = new FilesystemLoader(__DIR__.'/views/%name%');

$templating = new PhpEngine(new TemplateNameParser(), $filesystemLoader);
$templating->set(new SlotsHelper());

echo $templating->render('hello.php', ['firstname' => 'Fabien']);

// hello.php
Hello, <?= $view->escape($firstname) ?>!
```
