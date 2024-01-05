<?php

function findFile($dir, $filename) {
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $result = findFile($path, $filename);
                if ($result) {
                    return $result;
                }
            } else {
                if ($file === $filename) {
                    return $path;
                }
            }
        }
    }

    return false;
}

$dir = '/path/to/your/directory'; // замените на актуальный путь к вашему каталогу
$filename = 'yourfilename.txt'; // замените на название искомого файла
$result = findFile($dir, $filename);
if ($result) {
    echo "Файл найден: " . $result;
} else {
    echo "Файл не найден";
}
