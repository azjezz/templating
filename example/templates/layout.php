<html>
    <head>
        <title><?= $this->get('slots')->get('title', 'Application') ?></title>
    </head>
    <body>
        <?= $this->get('slots')->get('_content') ?>
    </body>
</html>
