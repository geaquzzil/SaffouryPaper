<?php



namespace Etq\Restful\Repository;


use Etq\Restful\Repository\BaseRepository;



class PermissionRepository extends BaseRepository
{

    public function getQueryLevelPermssion(): string
    {
        return "SELECT *
                 FROM `permissions_levels` 
                 WHERE userlevelid = :levelID  AND table_name = :tableName";
    }

    public function getPermission(int $levelID, string $tableName,)
    {

        $query = $this->getQueryLevelPermssion();
        $statement = $this->database->prepare($query);
        $statement->execute([':tableName' => $tableName, ':levelID' => $levelID]);
        $permission = $statement->fetch();
        return $permission;
    }

    // public function get
}
