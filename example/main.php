<?php

use Hype\Helper\SlotsHelper;
use Hype\Loader\FilesystemLoader;
use Hype\PHPEngine;
use Hype\TemplateNameParser;
use Psl\Async;
use Psl\IO;

require __DIR__ . '/../vendor/autoload.php';

function hype(): void
{
    $parser = new TemplateNameParser();
    $loader = new FilesystemLoader([
        __DIR__ . '/templates/%name%',
    ]);

    $engine = new PHPEngine($parser, $loader, [
        new SlotsHelper()
    ]);

    $time = microtime(true);

    [$index] = Async\parallel([
        static fn() => $engine->render('index.php'),
        static fn() => $engine->render('index.php'),
        static fn() => $engine->render('contact.php'),
        static fn() => $engine->render('contact.php'),
    ]);

    $duration = microtime(true) - $time;

    IO\write_line('Hype: rendered "%s", and "%s" twice, in %f second(s).', 'index.php', 'contact.php', $duration);
    IO\write_line('');

    IO\write_line($index);
}

function symfony_templating(): void
{
    $parser = new \Symfony\Component\Templating\TemplateNameParser();
    $loader = new \Symfony\Component\Templating\Loader\FilesystemLoader([
        __DIR__ . '/templates/%name%',
    ]);

    $engine = new \Symfony\Component\Templating\PhpEngine($parser, $loader, [
        new \Symfony\Component\Templating\Helper\SlotsHelper()
    ]);

    $time = microtime(true);

    [$index] = Async\parallel([
        static fn() => $engine->render('index.php'),
        static fn() => $engine->render('index.php'),
        static fn() => $engine->render('contact.php'),
        static fn() => $engine->render('contact.php'),
    ]);

    $duration = microtime(true) - $time;

    IO\write_line('Symfony: rendered "%s", and "%s" twice, in %f second(s).', 'index.php', 'contact.php', $duration);
    IO\write_line('');

    IO\write_line($index);
}

Async\main(static function(): int {
    hype();

    symfony_templating();

    return 0;
});
