<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;



class NotificationRepository extends BaseRepository
{
    
    public function isNotificationDisabled()
    {
        $sql = " SELECT DISABLE_NOTIFICATIONS FROM " . SETTING;
        $result = $this->getFetshTableWithQuery($sql);
        $result = $result["DISABLE_NOTIFICATIONS"];
        return $result === 1 || $result == 1;
    }
    /**
     * Registers a new notifcication to send 
     *
     * By default it send text to all devices that registered in the server
     * if tablename then send it to the table ex.. customers
     * if tablename not null 
     */
    public function doNotifcationGeneral($obj, ?string $tableName = null, ?int $iD = null)
    {
        if ($this->isNotificationDisabled()) {
            throw new \Exception('notification service is disable contact admin to enable it ');
        }
        $isGeneralToAll =  !$tableName && !$iD;
        echo "notification is send to all ?  $isGeneralToAll ";
        if ($isGeneralToAll) {
        }
        $isGeneralToTableName = $tableName && !$iD;
        echo "notification is send to tableName ?  $isGeneralToTableName ";

        if ($isGeneralToTableName) {
        }
        $isToSpecificUser = $tableName && $iD;
        echo "notification is send to user name only ?  $isToSpecificUser ";
        if ($isToSpecificUser) {

        }
    }
}
