<?php

declare(strict_types=1);

namespace Etq\Restful\Controller\Admin;

use Etq\Restful\Controller\BaseController;
use Etq\Restful\Database\DBBackupAndRestore;
use Etq\Restful\Helpers;
use Etq\Restful\Repository\Repository;
use Etq\Restful\Repository\Options;
use Etq\Restful\Repository\SearchOption;
use Etq\Restful\Repository\SortOption;
use Etq\Restful\Repository\SearchType;
use Etq\Restful\Repository\SortType;
use Etq\Restful\Security\MCrypt;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

class DatabaseController extends BaseController
{

    // if ($document[0]->exists) {
    //     $payload[] = $document[0]->fileOutput();
    //     $file = $document[0]->file_url;
    //     $openFile = fopen($file, 'rb');
    //     $stream = new \Slim\Http\Stream($openFile);
    //     return $response->withStatus(200)
    //         ->withHeader('Content-Type', 'application/force-download')
    //         ->withHeader('Content-Type', 'application/octet-stream')
    //         ->withHeader('Content-Type', 'application/download')
    //         ->withHeader('Content-Description', 'File Transfer')
    //         ->withHeader('Content-Transfer-Encoding', 'binary')
    //         ->withHeader('Content-Disposition', 'attachment; filename="' . basename($file) . '"')
    //         ->withHeader('Expires', '0')
    //         ->withHeader('Content-Length', filesize($file))
    //         ->withHeader('Cache-Control', 'must-revalidate')
    //         ->withHeader('Pragma', 'public')
    //         ->withBody($stream)
    //         ->withJson([
    //             'message' => 'Success',
    //             'code' => 204,
    //             'documents' => $payload
    //         ]);
    // } 
    private function backup(Response $response)
    {
        $db = new DBBackupAndRestore();
        $result = $db->backupTables();
        if ($result) {
            $mcrypt = new MCrypt();
            $db->obfPrint('Backup result: ' . $result, 1);
            // echo $db->content;
            $txtOfFile = $mcrypt->encrypt($db->content);
            $compress = gzencode(gzcompress($txtOfFile, 9));
            file_put_contents($db->getFileName(), $compress, FILE_APPEND | LOCK_EX);
            $stream = new \Slim\Http\Stream(fopen($db->getFileName(), 'r'));
            $response->write($compress);
            // $response->write("$openFile ");
            return $response
                // ->withHeader('Content-Type', 'application/force-download')
                ->withHeader('Content-Type', 'application/x-gzip')
                // ->withHeader('Content-Type', 'application/octet-stream')
                // ->withHeader('Content-Type', 'application/download')
                ->withHeader('Content-Description', 'File Transfer')
                ->withHeader('Content-Transfer-Encoding', 'binary')
                ->withHeader('Content-disposition', "attachment; filename=\"" . $db->backupFile . "\"")
                ->withHeader('Expires', '0')
                ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                // ->withHeader('key', $uuid)
                // ->withBody($stream)
                // ->withBody($stream)
            ; // all stream contents will be sent to the response
            // echo "attachment; filename=" . $db->backupFile;
            // echo gzencode(gzcompress($txtOfFile, 9));
            // foreach ($response->getHeaders() as $name => $values) {
            //     foreach ($values as $index => $value) {
            //         echo $name . "   " . $value . "\n";
            //         // header(sprintf('%s: %s', $name, $value), $index === 0);
            //     }
            // }
            $newresponse = $response

                // ->withHeader('Content-Type', 'application/force-download')
                // ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Type', 'application/x-gzip')
                ->withHeader('Content-Transfer-Encoding', 'Binary')
                ->withHeader('Content-disposition', "attachment; filename=" . $db->backupFile . ".zip")
                ->withHeader('Cache-Control', 'must-revalidate')
                ->write($compress);
            foreach ($newresponse->getHeaders() as $name => $values) {
                foreach ($values as $index => $value) {
                    // echo $name . "   " . $value . "\n";
                    // header(sprintf('%s: %s', $name, $value), $index === 0);
                }
            }
            return $newresponse;
            // ->withJson(['message' => 'Success'], 200, JSON_PRETTY_PRINT);
        } else {
            throw new Exception("Error while backup");
        }
    }
    private function restore()
    {
        // $mcrypt = new MCrypt();
        // $txtOfFile = $mcrypt->decrypt(gzuncompress($txtOfFile));
        // error_reporting(E_ALL);
        // Set script max execution time
        // set_time_limit(900); // 15 minutes
        // $restoreDatabase = new Restore_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        //$result = $restoreDatabase->restoreDbText($txtOfFile) ? 'OK' : 'KO';
        //   $restoreDatabase->obfPrint("Restoration result: ".$result, 1);
    }
    public function __invoke(Request $request, Response $response): Response
    {
        parent::init($request);
        // echo "{$this->tableName} is ";
        if ($this->tableName == "backup") {
            // echo "{$this->tableName} is ";
            return  $this->backup($response);
        } else {
            return $this->restore();
        }



        // require_once('Utils/db_backupAndRestore.php');
        // require_once('cryptor.php');
        // $mcrypt = new MCrypt();

        // /**
        //  * Instantiate Backup_Database and perform backup
        //  */
        // // Report all errors
        // error_reporting(E_ALL);
        // // Set script max execution time
        // set_time_limit(900); // 15 minutes
        // $backupDatabase = new Backup_Database(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, CHARSET);
        // $result = $backupDatabase->backupTables(TABLES, BACKUP_DIR) ? 'OK' : 'KO';
        // $backupDatabase->obfPrint('Backup result: ' . $result, 1);
        // // Use $output variable for further processing, for example to send it by email
        // //$output = $backupDatabase->getOutput();
        // // header('Content-Type: application/octet-stream'); 
        // header('Content-Type: application/x-gzip');
        // header("Content-Transfer-Encoding: Binary");
        // header("Content-disposition: attachment; filename=\"" . $backupDatabase->backupFile . "\"");
        // $txtOfFile = $mcrypt->encrypt($backupDatabase->content);
        // echo gzencode(gzcompress($txtOfFile, 9));
        // exit;

        return $this->textResponse($response, "Notification");
    }
}
