FrchoBackupManagerBundle
===================

A simple database backup manager for Symfony 4 with support for S3, Rackspace, Dropbox, FTP, SFTP.

This package pulls in the framework agnostic [Backup Manager](https://github.com/backup-manager/backup-manager) and provides seamless integration with **Symfony**. 

for previous version of symfony use tag 1.0.1

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require frcho/backup-manager "dev-master"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


Step 2: Configure your databases and filesystems
------------------------------------------------

```yaml
# config/packages/frcho_backup_manager.yaml

frcho_backup_manager:
    database:
        development:
            type: mysql
            host: localhost
            port: 3306
            user: root
            pass: password
            database: test
        production:
            type: postgresql
            host: localhost
            port: 5432
            user: postgres
            pass: password
            database: test
    storage:
        local:
            type: Local
            root: /path/to/working/directory
        s3:
            type: AwsS3
            key:
            secret:
            region: us-east-1
            version: latest
            bucket:
            root:
        rackspace:
            type: Rackspace
            username:
            password:
            container:
        dropbox:
            type: Dropbox
            token:
            key:
            secret:
            app:
            root:
        ftp:
            type: Ftp
            host:
            username:
            password:
            root:
            port: 21
            passive: true
            ssl: true
            timeout: 30
        sftp:
            type: Sftp
            host:
            username:
            password:
            root:
            port: 21
            timeout: 10
            privateKey:
```

Usage
=====

Backup to / restore from any configured database.
-------------------------------------------------

Backup the development database to `Amazon S3`. The S3 backup path will be `test/backup.sql.gz` in the end, when `gzip` is done with it.

```php
$container->get('backup_manager')->makeBackup()->run('development', array(new Destination('s3', 'test/backup.sql.gz')), 'gzip');
```
Backup the development database to `Local`. The local backup path will be `test/backup.sql.gz` in the end, when `gzip` is done with it.
```php
$container->get('backup_manager')->makeBackup()->run('development', array(new Destination('local', 'test/backup.sql.gz')), 'gzip');
```

Note: if you want upload files without compress pass like parameter a string 'null'  instead of `gzip`


Backup to / restore from any configured filesystem.
---------------------------------------------------

Restore the database file `test/backup.sql.gz` from `Amazon S3` to the `development` database.

```php
$this->container->get('backup_manager')->makeRestore()->run('s3', 'test/backup.sql.gz', 'development', 'gzip');
```

> This package does not allow you to backup from one database type and restore to another. A MySQL dump is not compatible with PostgreSQL.

Requirements
============

- PHP 7.0
- MySQL support requires `mysqldump` and `mysql` command-line binaries
- PostgreSQL support requires `pg_dump` and `psql` command-line binaries
- Gzip support requires `gzip` and `gunzip` command-line binaries