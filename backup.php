<?php

class FileBackupData 
{
    public string $sourcePath;
    public iterable $excludePaths = [];
}

class MysqlBackupData
{
    public string $host;
    public int $port = 3306;
    public string $user;
    public string $password;
    public iterable $databases = [];
}

interface BackupProcessInterface
{    
    public function execute(string $dest);
}

class BackupCopiesData
{
    public BackupProcessInterface $backupProcess;
    public string $dateFormat;
    public string $copiesPath;
    public int $maxCopies;
    public \DateTimeInterface $maxOldDate;
    public \DateTimeInterface $dateFrom;
}

class FileBackupProcess implements BackupProcessInterface
{  
    public FileBackupData $backupData;
    
    public function execute(string $destinationPath)
    {
        $data = $this->backupData;
        $excludeStr = '';
        foreach ($data->excludePaths as $path) {
            $excludeStr .= " --exclude='" . $path . "'";
        }
        exec("rsync -as --delete$excludeStr '{$data->sourcePath}' '$destinationPath'");
    }
}

class MysqlBackupProcess implements BackupProcessInterface
{  
    public MysqlBackupData $backupData;
    
    public function execute(string $destinationPath)
    {
        $data = $this->backupData;
        $paramsString = "-h{$data->host} --port {$data->port} -u{$data->user} -p{$data->password}";
        $databases = [];
        exec("mysql $paramsString -e 'show databases' -s --skip-column-names", $databases);
        foreach ($databases as $database) {
            if (!$data->databases || in_array($database, $data->databases)) {
                exec("rm -fr $destinationPath/*");
                exec("mysqldump $paramsString --single-transaction=true $database | zip $destinationPath/$database.sql.zip -");
            }
        }
    }
}

class BackupCopiesProcess
{
    /** @var BackupCopiesData[] $backupCopiesDatas */
    public iterable $backupCopiesDatas;
    
    public function execute()
    {
        foreach ($this->backupCopiesDatas as $backupCopiesData) {
            $copiesCount = 0;
            $lastDate = null;
            
            if (!file_exists($backupCopiesData->copiesPath)) {
                mkdir($backupCopiesData->copiesPath);
            }
            $dirs = scandir($backupCopiesData->copiesPath);

            foreach ($dirs as $dirName) {
                $dir = $backupCopiesData->copiesPath . '/' . $dirName;
                if (is_dir($dir)) {
                    $date = \DateTime::createFromFormat($backupCopiesData->dateFormat, $dirName);
                    if ($date) {            
                        $copiesCount++;
                        if (!$lastDate || $date > $lastDate) {
                            $lastDate = $date;
                        }
                        
                        if ($date < $backupCopiesData->maxOldDate) {
                            $newDir = $backupCopiesData->copiesPath . '/' . date($backupCopiesData->dateFormat);
                            $backupCopiesData->backupProcess->execute($newDir);
                        }
                    }
                }
            }

            if ($copiesCount < $backupCopiesData->maxCopies && (!$lastDate || $lastDate < $backupCopiesData->dateFrom)) {
                $newDir = $backupCopiesData->copiesPath . '/' . date($backupCopiesData->dateFormat);
                mkdir($newDir);
                $backupCopiesData->backupProcess->execute($newDir);
            }
        }        
    }
}
