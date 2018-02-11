<?php

// Register application loader
$loader = new Phalcon\Loader();
$loader->registerNamespaces(['app' => APP_DIR]);
$loader->register();
