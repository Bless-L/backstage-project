<?php
include 'vaild.php';

$valid = new Valite();
$valid->setImage('8.jpg');
$valid->seiZimu($zimu);

echo $valid->getResult();
