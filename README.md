# Files and mysql databases backup utility

Transfers only new or changed files. Full synchronization.
Requires PHP version >= 8.0.
For files backup you need `rsync` and `ssh` installed with added ssh key. For mysql - `mysqldump` and `zip` utilites.
You may create your custom process for any storage.
Such script can be executed every day by cron.

Usage:
```php
<?php

require 'backup.php';

//creating file backup data

$fileBackupData = new FileBackupData(
  //remote ssh connection or local path
  sourcePath: 'user@host:/your/path',
  excludePaths: ['/dev', '/proc', '/sys', '/tmp', '/run'],
);


$fileBackupProcess = new FileBackupProcess($fileBackupData);

$fileBackupCopiesData = new BackupCopiesData(
  backupProcess = $fileBackupProcess,
  //copies save path
  copiesPath: '/backup-path/copies',
  //the name of copy folder
  dateFormat: 'Y-m-d',
  maxCopies: 3,
  //the oldest copy will be synchronized with last data (file transfer economy)
  maxOldDate: new DateTime('-3 week'),
  //copy interval
  dateFrom: = new DateTime('-1 week'),
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
  dateFormat: 'Y-m-d',
  copiesPath: '/backup-path/db',
  maxCopies: 20,
  maxOldDate: new DateTime('-20 days'),
  dateFrom: new DateTime('-20 hours'),
);

//start backup copies process

$backupCopiesProcess = new BackupCopiesProcess([$fileBackupCopiesData, $mysqlBackupCopiesData]);
$backupCopiesProcess->execute();

//start a simple backup process

$fileBackupProcess->execute("/backup-path/last");
```
