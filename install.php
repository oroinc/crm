<?php

$opts = getopt('p:');

$command = $opts['p'] . ' app/console doctrine:schema:update --force';
system($command);

