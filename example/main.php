<?php

use Hype\Helper\SlotsHelper;
use Hype\Loader\FilesystemLoader;
use Hype\PHPEngine;
use Hype\TemplateNameParser;
use Psl\IO;
use Psl\Async;

require __DIR__ . '/../vendor/autoload.php';

Async\main(static function (): void {
    $parser = new TemplateNameParser();
    $loader = new FilesystemLoader([
        __DIR__ . '/templates/%name%',
    ]);

    $engine = new PHPEngine($parser, $loader, [
        new SlotsHelper()
    ]);

    $time = microtime(true);

    [$index, $contact] = Async\parallel([
        static fn() => $engine->render('index.php'),
        static fn() => $engine->render('index.php'),
        static fn() => $engine->render('contact.php'),
        static fn() => $engine->render('contact.php'),
    ]);

    $duration = microtime(true) - $time;

    IO\write_line('Rendered "%s", and "%s" twice, in %f second(s).', 'index.php', 'contact.php', $duration);
    IO\write_line('');

    IO\write_error_line($index);
    IO\write_error_line($contact);
});
