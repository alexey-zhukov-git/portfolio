<?php

$initialList = [3, 7];

/*

Итеративный вариант:

for($i = 2; $i < 5; $i++) {
    $sum = $initialList[$i - 1] + $initialList[$i - 2];
    $initialList[] = $sum;
}

var_dump($initialList);

Далее рекурсивный вариант:

*/

function add(array $initialList, int $i): void
{
    $sum = $initialList[$i - 1] + $initialList[$i - 2];
    $initialList[] = $sum;
    $i++;
    if(count($initialList) == 5) {
        var_dump($initialList);
    } else {
        add($initialList, $i);
    }   
}

add($initialList, 2);

/*

Результат выполнения данного примера в обоих случаях:

array(5) {
  [0]=>
  int(3)
  [1]=>
  int(7)
  [2]=>
  int(10)
  [3]=>
  int(17)
  [4]=>
  int(27)
}

*/