<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo '<style>del {color:gray}; ins {color:green}</style>';



echo diffline('StackOverflow', 'ServerFault');
?>