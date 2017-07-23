<?php
require __DIR__ . '/../src/TheseusParse.php';

use lincanbin\TheseusParse;

$parser = new TheseusParse();
$parser->parse(__DIR__ . '/home/Home.tss');
var_dump($parser);