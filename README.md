# hswitch

Simple command line tool to switch hosts in /etc/hosts

## Usage
```bash
Usage: hswitch hostname|all groupname|all [on (default)|off]
```

Sample hosts file

```bash
    # devel
    10.0.0.0 couchdb
    10.0.0.1 mysql

    # preprod
    # 11.0.0.0 couchdb
    # 11.0.0.1 mysql

    # prod
    # 12.0.0.0 couchdb
    # 12.0.0.1 mysql
```

Sample switching

```bash
    # switch couchdb to: preprod
    hswitch couchdb preprod

    # disable all mysql hosts
    hswitch mysql all off



```