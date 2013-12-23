<?php

$opts = getopt('p:');
ob_start();
$command = $opts['p'] . ' app/console doctrine:schema:update --force';
system($command);
$output = ob_get_contents();
ob_end_clean(); //Use this instead of ob_flush()
