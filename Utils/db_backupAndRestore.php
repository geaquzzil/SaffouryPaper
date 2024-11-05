<?php 
/**
 * This file contains the Backup_Database class wich performs
 * a partial or complete backup of any given MySQL database
 * @author Daniel López Azaña <daniloaz@gmail.com>
 * @version 1.0
 */
/**
 * Define database parameters here
 */
define("BACKUP_DIR", 'myphp-backup-files'); // Comment this line to use same script's directory ('.')
define("TABLES", '*'); // Full backup
//define("TABLES", 'table1, table2, table3'); // Partial backup
define("CHARSET", 'utf8');
define("GZIP_BACKUP_FILE", false); // Set to false if you want plain SQL backup files (not gzipped)
define("DISABLE_FOREIGN_KEY_CHECKS", true); // Set to true if you are having foreign key constraint fails
define("BATCH_SIZE", 1000); // Batch size when selecting rows from database in order to not exhaust system memory
                            // Also number of rows per INSERT statement in backup file

/**
 * The Backup_Database class
 */
class Backup_Database {
    /**
     * Host where the database is located
     */
    var $host;

    /**
     * Username used to connect to database
     */
    var $username;

    /**
     * Password used to connect to database
     */
    var $passwd;

    /**
     * Database to backup
     */
    var $dbName;

    /**
     * Database charset
     */
    var $charset;

    /**
     * Database connection
     */
    var $conn;

    /**
     * Backup directory where backup files are stored 
     */
    var $backupDir;

    /**
     * Output backup file
     */
    var $backupFile;

    /**
     * Use gzip compression on backup file
     */
    var $gzipBackupFile;

    /**
     * Content of standard output
     */
    var $output;

    /**
     * Disable foreign key checks
     */
    var $disableForeignKeyChecks;

    /**
     * Batch size, number of rows to process per iteration
     */
    var $batchSize;
    
    var $content;

    /**
     * Constructor initializes database
     */
    public function __construct($host, $username, $passwd, $dbName, $charset = 'utf8') {
        $this->content                 ="";
        $this->host                    = $host;
        $this->username                = $username;
        $this->passwd                  = $passwd;
        $this->dbName                  = $dbName;
        $this->charset                 = $charset;
        $this->conn                    = $this->initializeDatabase();
        $this->backupDir               = BACKUP_DIR ? BACKUP_DIR : '.';
        $this->backupFile              = '_'.$this->dbName.'_'.date('h-i-sa')."_".date('d-m-Y').'.sql';
        $this->gzipBackupFile          = defined('GZIP_BACKUP_FILE') ? GZIP_BACKUP_FILE : true;
        $this->disableForeignKeyChecks = defined('DISABLE_FOREIGN_KEY_CHECKS') ? DISABLE_FOREIGN_KEY_CHECKS : true;
        $this->batchSize               = defined('BATCH_SIZE') ? BATCH_SIZE : 1000; // default 1000 rows
        $this->output                  = '';
    }

    protected function initializeDatabase() {
        try {
            $conn = mysqli_connect($this->host, $this->username, $this->passwd, $this->dbName);
            if (mysqli_connect_errno()) {
                throw new Exception('ERROR connecting database: ' . mysqli_connect_error());
                die();
            }
            if (!mysqli_set_charset($conn, $this->charset)) {
                mysqli_query($conn, 'SET NAMES '.$this->charset);
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            die();
        }

        return $conn;
    }

    /**
     * Backup the whole database or just some tables
     * Use '*' for whole database or 'table1 table2 table3...'
     * @param string $tables
     */
    public function backupTables($tables = '*') {
        try {
            /**
             * Tables to export
             */
            if($tables == '*') {
                $tables = array();
              //  $result = mysqli_query($this->conn, 'SHOW TABLES');
              $result = mysqli_query($this->conn, ' show full tables where Table_type = "BASE TABLE"');
                while($row = mysqli_fetch_row($result)) {
                    $tables[] = $row[0];
                }
            } else {
                $tables = is_array($tables) ? $tables : explode(',', str_replace(' ', '', $tables));
            }

            $sql = 'CREATE DATABASE IF NOT EXISTS `'.$this->dbName."`;\n\n";
            $sql .= 'USE `'.$this->dbName."`;\n\n";

            /**
             * Disable foreign key checks 
             */
            if ($this->disableForeignKeyChecks === true) {
                $sql .= "SET foreign_key_checks = 0;\n\n";
            }

            /**
             * Iterate tables
             */
            foreach($tables as $table) {
                
                
                $this->obfPrint("Backing up `".$table."` table...".str_repeat('.', 50-strlen($table)), 0, 0);

                /**
                 * CREATE TABLE
                 */
                $sql .= 'DROP TABLE IF EXISTS `'.$table.'`;';
                $row = mysqli_fetch_row(mysqli_query($this->conn, 'SHOW CREATE TABLE `'.$table.'`'));
                $sql .= "\n\n".$row[1].";\n\n";

                /**
                 * INSERT INTO
                 */

                $row = mysqli_fetch_row(mysqli_query($this->conn, 'SELECT COUNT(*) FROM `'.$table.'`'));
                $numRows = $row[0];

                // Split table in batches in order to not exhaust system memory 
                $numBatches = intval($numRows / $this->batchSize) + 1; // Number of while-loop calls to perform

                for ($b = 1; $b <= $numBatches; $b++) {
                    
                    $query = 'SELECT * FROM `' . $table . '` LIMIT ' . ($b * $this->batchSize - $this->batchSize) . ',' . $this->batchSize;
                    $result = mysqli_query($this->conn, $query);
                    $realBatchSize = mysqli_num_rows ($result); // Last batch size can be different from $this->batchSize
                    $numFields = mysqli_num_fields($result);

                    if ($realBatchSize !== 0) {
                        $sql .= 'INSERT INTO `'.$table.'` VALUES ';

                        for ($i = 0; $i < $numFields; $i++) {
                            $rowCount = 1;
                            while($row = mysqli_fetch_row($result)) {
                                $sql.='(';
                                for($j=0; $j<$numFields; $j++) {
                                    if (isset($row[$j])) {
                                        $row[$j] = addslashes($row[$j]);
                                        $row[$j] = str_replace("\n","\\n",$row[$j]);
                                        $row[$j] = str_replace("\r","\\r",$row[$j]);
                                        $row[$j] = str_replace("\f","\\f",$row[$j]);
                                        $row[$j] = str_replace("\t","\\t",$row[$j]);
                                        $row[$j] = str_replace("\v","\\v",$row[$j]);
                                        $row[$j] = str_replace("\a","\\a",$row[$j]);
                                        $row[$j] = str_replace("\b","\\b",$row[$j]);
                                        
                                        if ($row[$j] == 'true' or $row[$j] == 'false' or preg_match('/^-?[0-9]+$/', $row[$j]) or $row[$j] == 'NULL' or $row[$j] == 'null') {
                                            if(preg_match('#^0#', $row[$j]))
                                                $sql .= "'".$row[$j]."'";
                                            else
                                                $sql .= $row[$j];
                                        } else {
                                            $sql .= '"'.$row[$j].'"' ;
                                        }
                                    } else {
                                        $sql.= 'NULL';
                                    }
    
                                    if ($j < ($numFields-1)) {
                                        $sql .= ',';
                                    }
                                }
    
                                if ($rowCount == $realBatchSize) {
                                    $rowCount = 0;
                                    $sql.= ");\n"; //close the insert statement
                                } else {
                                    $sql.= "),\n"; //close the row
                                }
    
                                $rowCount++;
                            }
                        }
    
                        $this->saveFile($sql);
                        $sql = '';
                    }
                }

                /**
                 * CREATE TRIGGER
                 */

                // Check if there are some TRIGGERS associated to the table
                /*$query = "SHOW TRIGGERS LIKE '" . $table . "%'";
                $result = mysqli_query ($this->conn, $query);
                if ($result) {
                    $triggers = array();
                    while ($trigger = mysqli_fetch_row ($result)) {
                        $triggers[] = $trigger[0];
                    }
                    
                    // Iterate through triggers of the table
                    foreach ( $triggers as $trigger ) {
                        $query= 'SHOW CREATE TRIGGER `' . $trigger . '`';
                        $result = mysqli_fetch_array (mysqli_query ($this->conn, $query));
                        $sql.= "\nDROP TRIGGER IF EXISTS `" . $trigger . "`;\n";
                        $sql.= "DELIMITER $$\n" . $result[2] . "$$\n\nDELIMITER ;\n";
                    }

                    $sql.= "\n";

                    $this->saveFile($sql);
                    $sql = '';
                }*/
 
                $sql.="\n\n";

                $this->obfPrint('OK');
            }

            /**
             * Re-enable foreign key checks 
             */
            if ($this->disableForeignKeyChecks === true) {
                $sql .= "SET foreign_key_checks = 1;\n";
            }

            $this->saveFile($sql);

            if ($this->gzipBackupFile) {
                $this->gzipBackupFile();
            } else {
                $this->obfPrint('Backup file succesfully saved to ' . $this->backupDir.'/'.$this->backupFile, 1, 1);
            }
        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Save SQL to file
     * @param string $sql
     */
    protected function saveFile(&$sql) {
        if (!$sql) return false;

        try {

            if (!file_exists($this->backupDir)) {
                mkdir($this->backupDir, 0777, true);
            }
            
        //    file_put_contents($this->backupDir.'/'.$this->backupFile, $sql, FILE_APPEND | LOCK_EX);
             $this->content = $this->content.$sql;
       //     header('Content-Type: application/octet-stream');   header("Content-Transfer-Encoding: Binary"); header("Content-disposition: attachment; filename=\"".$this->backupFile."\"");echo $sql;  exit;

        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }

        return true;
    }

    /*
     * Gzip backup file
     *
     * @param integer $level GZIP compression level (default: 9)
     * @return string New filename (with .gz appended) if success, or false if operation fails
     */
    protected function gzipBackupFile($level = 9) {
        if (!$this->gzipBackupFile) {
            return true;
        }

        $source = $this->backupDir . '/' . $this->backupFile;
        $dest =  $source . '.gz';

        $this->obfPrint('Gzipping backup file to ' . $dest . '... ', 1, 0);

        $mode = 'wb' . $level;
        if ($fpOut = gzopen($dest, $mode)) {
            if ($fpIn = fopen($source,'rb')) {
                while (!feof($fpIn)) {
                    gzwrite($fpOut, fread($fpIn, 1024 * 256));
                }
                fclose($fpIn);
            } else {
                return false;
            }
            gzclose($fpOut);
            if(!unlink($source)) {
                return false;
            }
        } else {
            return false;
        }
        
        $this->obfPrint('OK');
        return $dest;
    }

    /**
     * Prints message forcing output buffer flush
     *
     */
    public function obfPrint ($msg = '', $lineBreaksBefore = 0, $lineBreaksAfter = 1) {
        if (!$msg) {
            return false;
        }

        if ($msg != 'OK' and $msg != 'KO') {
            $msg = date("Y-m-d H:i:s") . ' - ' . $msg;
        }
        $output = '';

        if (php_sapi_name() != "cli") {
            $lineBreak = "";
        } else {
            $lineBreak = "\n";
        }

        if ($lineBreaksBefore > 0) {
            for ($i = 1; $i <= $lineBreaksBefore; $i++) {
                $output .= $lineBreak;
            }                
        }

        $output .= $msg;

        if ($lineBreaksAfter > 0) {
            for ($i = 1; $i <= $lineBreaksAfter; $i++) {
                $output .= $lineBreak;
            }                
        }


        // Save output for later use
        $this->output .= str_replace('<br />', '\n', $output);

        $this->content=$this->content."\n/*!".$output." */\n ";
       // echo $output;


        if (php_sapi_name() != "cli") {
            if( ob_get_level() > 0 ) {
                ob_flush();
            }
        }

        $this->output .= " ";

        flush();
    }

    /**
     * Returns full execution output
     *
     */
    public function getOutput() {
        return $this->output;
    }
}
class Restore_Database {
    /**
     * Host where the database is located
     */
    var $host;

    /**
     * Username used to connect to database
     */
    var $username;

    /**
     * Password used to connect to database
     */
    var $passwd;

    /**
     * Database to backup
     */
    var $dbName;

    /**
     * Database charset
     */
    var $charset;

    /**
     * Database connection
     */
    var $conn;

    /**
     * Disable foreign key checks
     */
    var $disableForeignKeyChecks;

    var $jsonResponse;
    /**
     * Constructor initializes database
     */
    function __construct($host, $username, $passwd, $dbName, $charset = 'utf8') {
        $this->jsonResponse            = array(); $this->jsonResponse["tables"]=array();
        $this->host                    = $host;
        $this->username                = $username;
        $this->passwd                  = $passwd;
        $this->dbName                  = $dbName;
        $this->charset                 = $charset;
        $this->disableForeignKeyChecks = defined('DISABLE_FOREIGN_KEY_CHECKS') ? DISABLE_FOREIGN_KEY_CHECKS : true;
        $this->conn                    = $this->initializeDatabase();
        $this->backupDir               = defined('BACKUP_DIR') ? BACKUP_DIR : '.';
        $this->backupFile              = defined('BACKUP_FILE') ? BACKUP_FILE : null;
    }

    /**
     * Destructor re-enables foreign key checks
     */
    function __destructor() {
        /**
         * Re-enable foreign key checks 
         */
        if ($this->disableForeignKeyChecks === true) {
            mysqli_query($this->conn, 'SET foreign_key_checks = 1');
        }
    }

    protected function initializeDatabase() {
        try {
            $conn = mysqli_connect($this->host, $this->username, $this->passwd, $this->dbName);
            if (mysqli_connect_errno()) {
                throw new Exception('ERROR connecting database: ' . mysqli_connect_error());
                die();
            }
            if (!mysqli_set_charset($conn, $this->charset)) {
                mysqli_query($conn, 'SET NAMES '.$this->charset);
            }

            /**
             * Disable foreign key checks 
             */
            if ($this->disableForeignKeyChecks === true) {
                mysqli_query($conn, 'SET foreign_key_checks = 0');
            }

        } catch (Exception $e) {
            print_r($e->getMessage());
            die();
        }

        return $conn;
    }

    /**
     * Backup the whole database or just some tables
     * Use '*' for whole database or 'table1 table2 table3...'
     * @param string $tables
     */
    public function restoreDb() {
        try {
            $sql = '';
            $multiLineComment = false;

            $backupDir = $this->backupDir;
            $backupFile = $this->backupFile;

            /**
             * Gunzip file if gzipped
             */
            $backupFileIsGzipped = substr($backupFile, -3, 3) == '.gz' ? true : false;
            if ($backupFileIsGzipped) {
                if (!$backupFile = $this->gunzipBackupFile()) {
                    throw new Exception("ERROR: couldn't gunzip backup file " . $backupDir . '/' . $backupFile);
                }
            }

            /**
            * Read backup file line by line
            */
            $handle = fopen($backupDir . '/' . $backupFile, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $line = ltrim(rtrim($line));
                    if (strlen($line) > 1) { // avoid blank lines
                        $lineIsComment = false;
                        if (preg_match('/^\/\*/', $line)) {
                            $multiLineComment = true;
                            $lineIsComment = true;
                        }
                        if ($multiLineComment or preg_match('/^\/\//', $line)) {
                            $lineIsComment = true;
                        }
                        if (!$lineIsComment) {
                            $sql .= $line;
                            if (preg_match('/;$/', $line)) {
                                // execute query
                                if(mysqli_query($this->conn, $sql)) {
                                    if (preg_match('/^CREATE TABLE `([^`]+)`/i', $sql, $tableName)) {
                                        $this->obfPrint("Table succesfully created: `" . $tableName[1] . "`");
                                    }
                                    $sql = '';
                                } else {
                                    throw new Exception("ERROR: SQL execution error: " . mysqli_error($this->conn));
                                }
                            }
                        } else if (preg_match('/\*\/$/', $line)) {
                            $multiLineComment = false;
                        }
                    }
                }
                fclose($handle);
            } else {
                throw new Exception("ERROR: couldn't open backup file " . $backupDir . '/' . $backupFile);
            } 
        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }

        if ($backupFileIsGzipped) {
            unlink($backupDir . '/' . $backupFile);
        }

        return true;
    }
    public function restoreDbText($text) {
        try {
            $sql = '';
            $multiLineComment = false;
            file_put_contents("backup.sql", $text);
            /**
            * Read backup file line by line
            */
            $handle = fopen("backup.sql", "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $line = ltrim(rtrim($line));
                    if (strlen($line) > 1) { // avoid blank lines
                        $lineIsComment = false;
                        if (preg_match('/^\/\*/', $line)) {
                            $multiLineComment = true;
                            $lineIsComment = true;
                        }
                        if ($multiLineComment or preg_match('/^\/\//', $line)) {
                            $lineIsComment = true;
                        }
                        if (!$lineIsComment) {
                            $sql .= $line;
                            if (preg_match('/;$/', $line)) {
                                // execute query
                                if(mysqli_query($this->conn, $sql)) {
                                    if (preg_match('/^CREATE TABLE `([^`]+)`/i', $sql, $tableName)) {
                                         $this->jsonResponse["tables"][$tableName[1]]=true;
                                        //$this->obfPrint("Table succesfully created: `" . $tableName[1] . "`");
                                    }
                                    $sql = '';
                                } else {
                                    $this->jsonResponse["success"]=false;
                                    $this->jsonResponse["error"]="ERROR: SQL execution error: " . mysqli_error($this->conn);
                                    return false;
                                  //  throw new Exception("ERROR: SQL execution error: " . mysqli_error($this->conn));
                                }
                            }
                        } else if (preg_match('/\*\/$/', $line)) {
                            $multiLineComment = false;
                        }
                    }
                }
                fclose($handle);
            } else {
                 $this->jsonResponse["success"]=false;
                 $this->jsonResponse["error"]="ERROR: couldn't open backup file";
                 return false;
              //  throw new Exception("ERROR: couldn't open backup file backup.sql ");
            } 
        } catch (Exception $e) {
            $this->jsonResponse["success"]=false;
            $this->jsonResponse["error"]=($e->getMessage());
            return false;
        }
         $this->jsonResponse["success"]=true;
        unlink("backup.sql");
        return true;
    }
    /*
     * Gunzip backup file
     *
     * @return string New filename (without .gz appended and without backup directory) if success, or false if operation fails
     */
    protected function gunzipBackupFile() {
        // Raising this value may increase performance
        $bufferSize = 4096; // read 4kb at a time
        $error = false;

        $source = $this->backupDir . '/' . $this->backupFile;
        $dest = $this->backupDir . '/' . date("Ymd_His", time()) . '_' . substr($this->backupFile, 0, -3);

        $this->obfPrint('Gunzipping backup file ' . $source . '... ', 1, 1);

        // Remove $dest file if exists
        if (file_exists($dest)) {
            if (!unlink($dest)) {
                return false;
            }
        }
        
        // Open gzipped and destination files in binary mode
        if (!$srcFile = gzopen($this->backupDir . '/' . $this->backupFile, 'rb')) {
            return false;
        }
        if (!$dstFile = fopen($dest, 'wb')) {
            return false;
        }

        while (!gzeof($srcFile)) {
            // Read buffer-size bytes
            // Both fwrite and gzread are binary-safe
            if(!fwrite($dstFile, gzread($srcFile, $bufferSize))) {
                return false;
            }
        }

        fclose($dstFile);
        gzclose($srcFile);

        // Return backup filename excluding backup directory
        return str_replace($this->backupDir . '/', '', $dest);
    }

    /**
     * Prints message forcing output buffer flush
     *
     */
    public function obfPrint ($msg = '', $lineBreaksBefore = 0, $lineBreaksAfter = 1) {
        if (!$msg) {
            return false;
        }

        $msg = date("Y-m-d H:i:s") . ' - ' . $msg;
        $output = '';

        if (php_sapi_name() != "cli") {
            $lineBreak = "";
        } else {
            $lineBreak = "\n";
        }

        if ($lineBreaksBefore > 0) {
            for ($i = 1; $i <= $lineBreaksBefore; $i++) {
                $output .= $lineBreak;
            }                
        }

        $output .= $msg;

        if ($lineBreaksAfter > 0) {
            for ($i = 1; $i <= $lineBreaksAfter; $i++) {
                $output .= $lineBreak;
            }                
        }

        if (php_sapi_name() == "cli") {
            $output .= "\n";
        }

        echo $output;

        if (php_sapi_name() != "cli") {
            ob_flush();
        }

        flush();
    }
    
    public function getJson(){
        return  $this->jsonResponse;
    }
}
?>