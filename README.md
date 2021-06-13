# Files and mysql databases backup utility

Transfers only new or changed files. Full synchronization.
Requires PHP version >= 8.0.
For files backup you need `rsync` and `ssh` installed with added ssh key. For mysql - `mysqldump` and `zip` utilites.
You may create your custom process for any storage.
Such script can be executed every day by cron.

Usage:
```php
<?php

require 'backup-tool.php';

//creating file backup data

$fileBackupData = new FileBackupData(  
    sourcePath: 'user@host:/your/path', //remote ssh connection or local path
    excludePaths: ['/dev', '/proc', '/sys', '/tmp', '/run'],
);

$fileBackupProcess = new FileBackupProcess($fileBackupData);

//you may start a simple backup process just now
$fileBackupProcess->execute("/backup-path/last");

$fileBackupCopiesData = new BackupCopiesData(
    backupProcess: $fileBackupProcess,
    copiesPath: '/backup-path/copies', //copies save path  
    dateFormat: 'Y-m-d', //the name of copy folder (default: 'Y-m-d')
    maxCopies: 3,
    maxOldDate: new DateTime('-3 week'), //the oldest copy becomes the newest
    dateFrom: new DateTime('-1 week'), //copy interval
);

//creating mysql backup data

$mysqlBackupData = new MysqlBackupData(
    host: 'host',
    user: 'user',
    password: 'password',
    databases: ['my_database'],
);

$mysqlBackupProcess = new MysqlBackupProcess($mysqlBackupData);

$mysqlBackupCopiesData = new BackupCopiesData(
    backupProcess: $mysqlBackupProcess,
    copiesPath: '/backup-path/db',
    maxCopies: 20,
    maxOldDate: new DateTime('-20 days'),
    dateFrom: new DateTime('-20 hours'),
);

//start backup copies process

$backupCopiesProcess = new BackupCopiesProcess([$fileBackupCopiesData, $mysqlBackupCopiesData]);
$backupCopiesProcess->execute();
```
