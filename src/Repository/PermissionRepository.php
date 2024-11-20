<?php



namespace Etq\Restful\Repository;


use Etq\Restful\Repository\BaseRepository;



class PermissionRepository extends BaseRepository
{


    public function getQueryForSetting(): string
    {
        return "SELECT * FROM `settings` ";
    }
    public function getQueryLevelPermssion(): string
    {
        return "SELECT *
                 FROM `permissions_levels` 
                 WHERE userlevelid = :levelID  AND table_name = :tableName";
    }
    public function getSetting()
    {
        $query = $this->getQueryForSetting();
        $statement = $this->database->prepare($query);
        $statement->execute();
        $fetch = $statement->fetch();
        return $fetch;
    }

    public function getPermission(int $levelID, string $tableName,)
    {

        $query = $this->getQueryLevelPermssion();
        $statement = $this->database->prepare($query);
        $statement->execute([':tableName' => $tableName, ':levelID' => $levelID]);
        $fetch = $statement->fetch();
        return $fetch;
    }

    // public function get
}
