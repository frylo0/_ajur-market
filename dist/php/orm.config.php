<?php

require_once __DIR__ . '/ORM.php';
$orm = new ORM('localhost', 'phpmyadmin', 'Jyie-ha0r-kgie', 'just-field-cms');
$orm->table_prefix = 'jf-cms_';