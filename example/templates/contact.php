<?= $this->extend('layout.php') ?>

<?= $this->get('slots')->set('title', 'Contact') ?>

<div>
    <?php
        // simulate some non-blocking I/O opeartion.
        Psl\Async\sleep(0.5);
    ?>
    <h1>Contact us</h1>
</div>
