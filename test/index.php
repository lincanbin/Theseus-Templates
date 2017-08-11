<?php
require __DIR__ . '/../src/HTMLElement.php';
require __DIR__ . '/../src/TheseusParse.php';
require __DIR__ . '/../src/TheseusDOMDocument.php';
require __DIR__ . '/../src/TheseusDOM.php';
require __DIR__ . '/../src/Theseus.php';

use lincanbin\Theseus;
use lincanbin\TheseusDOMDocument;
$theseus = new Theseus();
$theseus->display(__DIR__ . '/home/Home.tss');