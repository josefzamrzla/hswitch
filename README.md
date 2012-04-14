# hswitch

Simple command line tool to switch hosts in /etc/hosts by environment groups.

FOR DEVELOPMENT USE ONLY, NOT FOR PRODUCTION USE!!!

## Installation

Copy script to some dir in your PATH and set up some rights
```bash
chmod 4755 /usr/bin/hswitch
chown root:root /usr/bin/hswitch
chmod u+s /usr/bin/hswitch
```

Script must be run under root rights!

## Usage
```bash
Usage: hswitch hostname|all groupname|all [on (default)|off]
```

Sample hosts file: couchdb and mysql links to "devel", elastic links to "preprod"

```bash
    # devel
    10.0.0.0 couchdb
    10.0.0.1 mysql
    # 10.0.0.2 elastic

    # preprod
    # 11.0.0.0 couchdb
    11.0.0.2 elastic
    # 11.0.0.1 mysql

    # prod
    # 12.0.0.0 couchdb
    # 12.0.0.1 mysql
    # 12.0.0.2 elastic
```

Sample switching

```bash
    # switch couchdb to: preprod
    hswitch couchdb preprod

    # disable all mysql hosts
    hswitch mysql all off

    # switch all hosts to: preprod
    hswitch all preprod

    # disable all "devel" hosts
    hswitch all devel off

    # disable all hosts
    hswitch all all off

```