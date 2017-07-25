<?php
require __DIR__ . '/../src/TheseusParse.php';
require __DIR__ . '/../src/Theseus.php';

use lincanbin\Theseus;

$theseus = new Theseus();
$theseus->display(__DIR__ . '/home/Home.tss');
