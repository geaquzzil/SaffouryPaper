<?php


namespace  Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Middleware\Auth;
use Exception;
use Mpdf\Tag\Option;

final class Repository extends BaseRepository
{
    private $getRequiredTableListObject = [
        AC_NAME => [AC_NAME_TYPE],
        CUSTOMS => [EMP],
        TYPE => [GD]
    ];
    private $getAllowedServerDataTabels = [
        QUA,
        TYPE,
        MAN,
        COUNTRY,

        GSM,
        EMP,
        USR,

        GD,
        GOV,
        CARGO,
        AC_NAME,
        AC_NAME_TYPE,
        CUSTOMS,
        CUR,
        CUST,
        USR
    ];

    private function getOptionRequiredObject($tableName)
    {
        $val = Helpers::isSetKeyFromObjReturnValue($this->getRequiredTableListObject, $tableName);
        if (is_null($val)) {
            return [];
        }
        return $val;
    }
    public function getServerData(?string $tableName = null, ?Auth $auth = null)
    {
        if (!is_null($tableName)) {
            if (!Helpers::searchInArray($tableName, $this->getAllowedServerDataTabels)) {
                throw new Exception("Permssion denied");
            }
            return $this->list(
                $tableName,
                null,
                Options::getInstance()->requireObjects($this->getOptionRequiredObject($tableName))
            );
        }
        $list = $this->getOptionRequiredObject($tableName);
        $option = empty($list) ? null : Options::getInstance()->requireObjects($list);

        $response[QUA] =  $this->list(QUA, null, $option);
        $response[TYPE] =  $this->list(TYPE, null, $option);

        $response[MAN] =  $this->list(MAN);
        $response[COUNTRY] =  $this->list(COUNTRY);
        $response[GSM] =  $this->list(GSM);
        $response[GD] =  $this->list(GD);
        $response[WARE] =  $this->list(WARE);

        if ($auth?->isEmployee()) {
            $response[GOV] =  $this->list(GOV, null, $option);
            $response[CARGO] =  $this->list(CARGO, null, $option);
            $response[AC_NAME_TYPE] =  $this->list(AC_NAME_TYPE, null, $option);
            $response[AC_NAME] =  $this->list(AC_NAME, null, $option);
            $response[CUSTOMS] =  $this->list(CUSTOMS, null, $option);
            $response[CUR] =  $this->list(CUR, null, $option);
            $response[CUST] = $this->list(CUST, null, $option);
            $response[USR] =  $this->list(USR, null, $option);
        }
        return $response;
    }

    // public function list() {}
    // public function checkAndGetNote(int $noteId): Note
    // {
    //     $query = 'SELECT * FROM `notes` WHERE `id` = :id';
    //     $statement = $this->database->prepare($query);
    //     $statement->bindParam(':id', $noteId);
    //     $statement->execute();
    //     $note = $statement->fetchObject(Note::class);
    //     if (! $note) {
    //         throw new \App\Exception\Note('Note not found.', 404);
    //     }

    //     return $note;
    // }

    /**
     * @return array<string>
     */
    public function getNotes(): array
    {
        $query = 'SELECT * FROM `notes` ORDER BY `id`';
        $statement = $this->database->prepare($query);
        $statement->execute();

        return (array) $statement->fetchAll();
    }

    public function getQueryNotesByPage(): string
    {
        return "
            SELECT *
            FROM `notes`
            WHERE `name` LIKE CONCAT('%', :name, '%')
            AND `description` LIKE CONCAT('%', :description, '%')
            ORDER BY `id`
        ";
    }

    /**
     * @return array<string>
     */
    public function getNotesByPage(
        int $page,
        int $perPage,
        ?string $name,
        ?string $description
    ): array {
        $params = [
            'name' => is_null($name) ? '' : $name,
            'description' => is_null($description) ? '' : $description,
        ];
        $query = $this->getQueryNotesByPage();
        $statement = $this->database->prepare($query);
        $statement->bindParam('name', $params['name']);
        $statement->bindParam('description', $params['description']);
        $statement->execute();
        $total = $statement->rowCount();

        return $this->getResultsWithPagination(
            $query,
            $page,
            $perPage,
            $params,
            $total
        );
    }

    // public function createNote(Note $note): Note
    // {
    //     $query = '
    //         INSERT INTO `notes`
    //             (`name`, `description`)
    //         VALUES
    //             (:name, :description)
    //     ';
    //     $statement = $this->database->prepare($query);
    //     $name = $note->getName();
    //     $desc = $note->getDescription();
    //     $statement->bindParam(':name', $name);
    //     $statement->bindParam(':description', $desc);
    //     $statement->execute();

    //     return $this->checkAndGetNote((int) $this->database->lastInsertId());
    // }

    // public function updateNote(Note $note): Note
    // {
    //     $query = '
    //         UPDATE `notes`
    //         SET `name` = :name, `description` = :description
    //         WHERE `id` = :id
    //     ';
    //     $statement = $this->database->prepare($query);
    //     $id = $note->getId();
    //     $name = $note->getName();
    //     $desc = $note->getDescription();
    //     $statement->bindParam(':id', $id);
    //     $statement->bindParam(':name', $name);
    //     $statement->bindParam(':description', $desc);
    //     $statement->execute();

    //     return $this->checkAndGetNote((int) $id);
    // }

    // public function deleteNote(int $noteId): void
    // {
    //     $query = 'DELETE FROM `notes` WHERE `id` = :id';
    //     $statement = $this->database->prepare($query);
    //     $statement->bindParam(':id', $noteId);
    //     $statement->execute();
    // }
}
