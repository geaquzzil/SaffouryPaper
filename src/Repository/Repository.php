<?php


namespace  Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Middleware\Auth;
use Etq\Restful\Middleware\Permissions\ListPermission;
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
    public function getChangedRecords($tableName, ?ListPermission $permission = null, ?Options $options = null)
    {
        // print_r($options->notFoundedColumns->all());
        // $query = $options->getQuery($tableName);

        // $this->getGrowthRate()

        // $options->getClone()->addStaticSelect("COUNT(*) as count")
        // $soso = $options->notFoundedColumns->get("SOSO", null);
        // echo "value is $soso";
        // print_r($soso);
        // if (is_null($options->notFoundedColumns->get("SOSO", null))) {
        //     throw new Exception("NOT SET ");
        // }

        return $this->list(
            $tableName,
            null,
            $options
                ->addStaticSelect("COUNT(*) as count")
                ->addOrderBy("count")
        );
    }
}
