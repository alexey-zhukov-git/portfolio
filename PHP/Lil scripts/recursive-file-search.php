<?php

$searchRoot = 'имя_директории';
$searchName = 'имя_файла';
$ds = DIRECTORY_SEPARATOR;

$it  = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($searchRoot));

$result = [];

foreach($it as $val) {
    if(is_file($val)) {
        $elements = explode($ds, $val);
        $lastElement = end($elements);
        if($lastElement == $searchName) {
            $result[] = $searchRoot . $ds . $it->getSubPath() . $ds . $lastElement . PHP_EOL;
        }
    }    
}

if($result) {
    var_dump($result);
} else {
    echo 'Такой файл не найден' . PHP_EOL;
}