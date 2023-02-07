<?php

require_once './vendor/autoload.php';

function loaderEntities($className)
{
    require_once './entities/' . $className . '.php';
}

spl_autoload_register('loaderEntities');