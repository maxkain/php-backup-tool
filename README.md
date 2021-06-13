# Files and mysql databases backup utility

For files backup you need `rsync` and `ssh` with added ssh key. For mysql - `mysqldump` and `zip` utilites

Usage:
```php

//creating file backup data

$fileBackupData = new FileBackupData();

//ssh connection
$fileBackupData->sourcePath = 'user@host:/any/path';

$fileBackupData->excludePaths = ['/dev', '/proc', '/sys', '/tmp', '/run'];

$fileBackupProcess = new FileBackupProcess();
$fileBackupProcess->backupData = $fileBackupData;

$fileBackupCopiesData = new BackupCopiesData();
$fileBackupCopiesData->backupProcess = $fileBackupProcess;

//copies save path
$fileBackupCopiesData->copiesPath = '/backup-path/copies';

//the name of copy folder
$fileBackupCopiesData->dateFormat = 'Y-m-d';
$fileBackupCopiesData->maxCopies = 3;

//the oldest copy will be synchronized with last data (file transfer economy)
$fileBackupCopiesData->maxOldDate = new DateTime('-2 week'); 

//copy interval
$fileBackupCopiesData->dateFrom = new DateTime('-1 week');

//creating mysql backup data

$mysqlBackupData = new MysqlBackupData();
$mysqlBackupData->host = 'host';
$mysqlBackupData->user = 'user';
$mysqlBackupData->password = 'password';
$mysqlBackupData->databases = ['my_database'];

$mysqlBackupProcess = new MysqlBackupProcess();
$mysqlBackupProcess->backupData = $mysqlBackupData;

$mysqlBackupCopiesData = new BackupCopiesData();
$mysqlBackupCopiesData->backupProcess = $mysqlBackupProcess;
$mysqlBackupCopiesData->dateFormat = 'Y-m-d';
$mysqlBackupCopiesData->copiesPath = '/backup-path/db';
$mysqlBackupCopiesData->maxCopies = 20;
$mysqlBackupCopiesData->maxOldDate = new DateTime('-30 days');
$mysqlBackupCopiesData->dateFrom = new DateTime('-20 hours');

//start backup copies process

$backupCopiesProcess = new BackupCopiesProcess();
$backupCopiesProcess->backupCopiesDatas = [$fileBackupCopiesData, $mysqlBackupCopiesData];
$backupCopiesProcess->execute();

//start a simple backup process

$fileBackupProcess->execute("/backup-path/last");
```
