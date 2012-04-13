#!/usr/bin/php
<?php

$HOSTS_FILE = "/etc/hosts";
$IP_ADDR_REGEXP = "[0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+";

$group = "__default";
$groups = array($group => array());
$rows = file($HOSTS_FILE);

foreach ($rows as $rowNr => &$row) {
    $row = trim($row);

    if (!strlen($row)) {
        unset($rows[$rowNr]);
        continue;
    }

    if ($row[0] == "#" && !preg_match("/[ ]?" . $IP_ADDR_REGEXP . "/", $row)) {
        $group = trim(str_replace("#", "", $row));
        $groups[$group] = array();
    } else {
        $groups[$group][] = $row;
    }
}

$hosts = array();
foreach ($groups as $groupName => $groupHostsArr) {
    $hosts[$groupName] = array();
    foreach ($groupHostsArr as $groupHosts) {
        $off = false;
        $ip = "unknown";
        foreach (preg_split("/[\s]+/", $groupHosts) as $part) {
            if ($part == "#") {
                $off = true;
                continue;
            }

            if ($part[0] == "#") {
                $off = true;
            }

            $part = trim(str_replace("#", "", $part));
            if (preg_match("/" . $IP_ADDR_REGEXP . "/", $part)) {
                $ip = $part;
            } else {
                $hosts[$groupName][$part] = $ip . ":" . ($off? 'off':'on');
            }
        }
    }
}

function setHostState($hostStr, $state)
{
    $parts = explode(":", $hostStr);
    return $parts[0] . ":" . $state;
}

function printFile(array $hosts)
{
    foreach ($hosts as $groupName => $groupHosts) {
        ksort($groupHosts);
        echo "#" . $groupName . "\n";
        foreach ($groupHosts as $groupHost => $hostStr) {
            $parts = explode(":", $hostStr);

            if ($parts[1] == "off") {
                echo "#";
            }

            echo $parts[0] . "\t" . $groupHost . "\n";
        }
        echo "\n";
    }
}

// origin
ob_start();
printFile($hosts);
file_put_contents("/tmp/hosts/origin", ob_get_contents());
ob_end_clean();

if ($argc >= 3 && $argc < 5) {

    $host = trim($argv[1]);
    $group = trim($argv[2]);
    $state = isset($argv[3])? trim($argv[3]): "on";

    if ($group != "all" && !isset($hosts[$group])) {
        echo "ERROR: unknown group: '" . $group . "'\n";
        exit -1;
    }

    if ($host != "all" && !isset($hosts[$group][$host])) {
        echo "ERROR: unknown host: '" . $host . "' in group: '" . $group . "'\n";
        exit -1;
    }

    if ($group == "all" && $state == "on") {
        echo "ERROR: cannot enable all hosts\n";
        exit -1;
    }

    if ($group == "all") {
        if ($host == "all") {
            // all all off
            foreach ($hosts as $groupName => $groupHosts) {
                foreach ($groupHosts as $groupHost => $hostStr) {
                    $hosts[$groupName][$groupHost] = setHostState($hosts[$groupName][$groupHost], "off");
                }
            }
        } else {
            // couch all off
            foreach ($hosts as $groupName => $groupHosts) {
                foreach ($groupHosts as $groupHost => $hostStr) {
                    if ($groupHost == $host) {
                        $hosts[$groupName][$groupHost] = setHostState($hosts[$groupName][$groupHost], "off");
                    }
                }
            }
        }
    } else {
        if ($host == "all") {
            // all devel on|off
            if ($state == "on") {
                $disable = array_keys($hosts[$group]);

                foreach ($hosts as $groupName => $groupHosts) {
                    foreach ($groupHosts as $groupHost => $hostStr) {
                        if (in_array($groupHost, $disable)) {
                            $hosts[$groupName][$groupHost] = setHostState($hosts[$groupName][$groupHost], "off");
                        }
                    }
                }
            }

            foreach ($hosts[$group] as $hostName => $hostStr) {
                $hosts[$group][$hostName] = setHostState($hosts[$group][$hostName], $state);
            }

        } else {
            // couch devel on|off
            if ($state == "on") {
                foreach($hosts as $groupName => $groupHosts) {
                    if (isset($hosts[$groupName][$host])) {
                        $hosts[$groupName][$host] = setHostState($hosts[$groupName][$host], "off");
                    }
                }
            }

            $hosts[$group][$host] = setHostState($hosts[$group][$host], $state);
        }
    }

} else {
    echo "Hosts switcher tool\nVersion: 0.1a\nCopyright (C) 2012 by Josef Zamrzla\n\n";
    echo "Usage: hswitch [hostname]|[all] [group]|[all] on (default)|off]\n\n";
}

// modified
ob_start();
printFile($hosts);
file_put_contents("/tmp/hosts/modified", ob_get_contents());
ob_end_clean();