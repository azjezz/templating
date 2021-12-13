<?= $this->extend('layout.php') ?>

<?= $this->get('slots')->set('title', 'Home') ?>

<div>
    <?php
        // simulate some non-blocking I/O opeartion.
        Psl\Async\sleep(0.6);
    ?>

    <h1>Hello, World!</h1>
</div>
