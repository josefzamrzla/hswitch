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
    hswitch hostname|all groupname|all [on (default)|off]
    hswitch -l hostname|groupname
    hswitch -add hostname ip [groupname]
    hswitch -replace hostname ip [groupname]
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

Add new host - mysql2 running on devel with IP 10.0.0.33

```bash

    hswitch -add mysql2 10.0.0.33 devel

```

Replace existent host - IP correction to 10.0.0.3

```bash

    hswitch -replace mysql2 10.0.0.3 devel

```

List of all groups where host 'mysql' is defined

```bash

    hswitch -l mysql

```

List of all hosts in 'devel' group

```bash

    hswitch -l devel

```