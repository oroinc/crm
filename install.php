<?php

$opts = getopt('p:');

$command = escapeshellarg($opts['p'] . ' app/console doctrine:schema:update --force --quiet');
system($command);

