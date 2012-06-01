<?php
// cli trigger
define("HOSTS_FILE", "/etc/hosts");

$hswitch = new Hswitch(new HostsParser(file_get_contents(HOSTS_FILE)));
$hswitch->processCall($argc, $argv);