<?php
class Hosts_FileParser
{
    const IP_ADDR_REGEXP = "|[0-9]+\\.[0-9]+\\.[0-9]+\\.[0-9]+|";
    const HOST_STATE_SEPARATOR = "::";
    const DEFAULT_GROUP_NAME = "__default";

    public function parse($fileContent)
    {
        $group = self::DEFAULT_GROUP_NAME;
        $parsedHosts = array();

        foreach (explode("\n", $fileContent) as $line) {
            $line = trim($line);

            if (strlen($line)) {

                if ($this->isHostsLine($line)) {
                    $hostsLine = $this->parseHostsLine($line);

                    foreach ($hostsLine as $ip => $hosts) {
                        foreach ($hosts as $host) {
                            $parsedHosts[$group][$ip][] = $host;
                        }
                    }
                } elseif (($groupName = $this->parseGroupName($line)) !== false) {
                    $group = $groupName;
                }

            }
        }

        return $parsedHosts;
    }

    public function parseGroupName($line)
    {
        $parts = explode("#", $this->replaceMultipleComments($line));

        if (isset($parts[1]) && !strlen(trim($parts[0])) && strlen(trim($parts[1])) > 0) {
            $subparts = explode(" ", trim($parts[1]));

            return trim($subparts[0]);
        }

        return false;
    }

    public function parseHostsLine($line)
    {
        $off = false;
        $ip = "unknown";
        $hosts = array();
        $line = trim(str_replace("#", " #", $line));
        foreach (preg_split("/[\s]+/", $this->replaceMultipleComments($line)) as $part) {
            if (strlen(trim($part))) {
                if ($part == "#") {
                    $off = true;
                    continue;
                }

                if ($part[0] == "#") {
                    $off = true;
                }

                $part = trim(str_replace("#", "", $part));
                if (preg_match(self::IP_ADDR_REGEXP, $part)) {
                    $ip = $part;
                    continue;
                }

                $hosts[$ip][] = $part . self::HOST_STATE_SEPARATOR . (($off || $ip == "unknown") ? "off" : "on");
            }
        }

        return $hosts;
    }

    public function isHostsLine($line)
    {
        return (preg_match(self::IP_ADDR_REGEXP, $line) !== 0);
    }

    public function replaceMultipleComments($line)
    {
        return preg_replace("|[#]+|", "#", $line);
    }
}