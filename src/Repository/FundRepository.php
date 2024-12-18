<?php



namespace Etq\Restful\Repository;

use Etq\Restful\Helpers;
use Etq\Restful\Repository\BaseRepository;



class FundRepository extends BaseRepository
{


    private function getJournalTableNameFromExisting($object)
    {
        $journalID = Helpers::getKeyValueFromObj($object, "isDirect");
        if ($journalID != 0) {
            $journalRecord = getFetshTableWithQuery("SELECT * FROM " . JO . " WHERE iD='$journalID'");
            $journalTable = Helpers::getKeyValueFromObj($journalRecord, "transaction");
            $str_arr = explode("_", $journalTable);
            if (Helpers::getKeyValueFromObj($object, "iD") == Helpers::getKeyValueFromObj($journalRecord, "fromAccount")) {
                return $str_arr[1];
            } else {
                // $secoundID = Helpers::getKeyValueFromObj($journalRecord, "fromAccount");
                return $str_arr[0];
            }
        }
    }
    public function checkToDeleteJournal(&$object)
    {
        if (!$this->isJournalRecord($object)) return;
        $journalTable = $this->getJournalTableNameFromExisting($object);
        deleteObject(Helpers::getKeyValueFromObj($object, JO), $journalTable, false);
        Helpers::setKeyValueFromObj($journal, "iD", Helpers::getKeyValueFromObj($object, "isDirect"));
        deleteObject($journal, JO, false);
    }
    private function isJournalRecord($object)
    {
        return Helpers::isSetKeyAndNotNullFromObj($object, 'isDirect');
    }
    private function checkToSetJournal(&$item)
    {
        if (!$this->isJournalRecord($item)) return;
        $journalID = Helpers::getKeyValueFromObj($item, "isDirect");
        if ($journalID != 0) {
            $journalRecord = getFetshTableWithQuery("SELECT * FROM " . JO . " WHERE iD='$journalID'");
            $journalTable = Helpers::getKeyValueFromObj($journalRecord, "transaction");
            $str_arr = explode("_", $journalTable);
            $secoundID;
            if (Helpers::getKeyValueFromObj($item, "iD") == $journalRecord["fromAccount"]) {
                $secoundID = Helpers::getKeyValueFromObj($journalRecord, "toAccount");
                $journalTable = $str_arr[1];
            } else {
                $secoundID = Helpers::getKeyValueFromObj($journalRecord, "fromAccount");
                $journalTable = $str_arr[0];
            }
            Helpers::setKeyValueFromObj($item, "transaction", $journalTable);
            Helpers::setKeyValueFromObj($item, JO, depthSearch($secoundID, $journalTable, -1, [], [CUST, EMP], null));
        }
    }
    private function getJournalTableNameFirst($object)
    {
        $journalTable = Helpers::getKeyValueFromObj($object, 'transaction');
        $str_arr = explode("_", $journalTable);
        return $str_arr[0];
    }
    private function getJournalTableName($object)
    {
        $journalTable = Helpers::getKeyValueFromObj($object, 'transaction');
        $str_arr = explode("_", $journalTable);
        return $str_arr[1];
    }
    function addJournalFromObject(&$object, $tableName)
    {

        $journalObject = Helpers::getKeyValueFromObj($object, JO);
        $journalObject = json_decode($journalObject, FALSE);
        $journalTable = $this->getJournalTableName($object);

        $currentObjectID = !Helpers::isNewRecord($object) ? Helpers::getKeyValueFromObj($object, ID) : $this->getLastIncrementID($tableName);
        $currentObjectJournalID = $this->getLastIncrementID($journalTable);

        $journal = array();
        $journal["iD"] = -1;
        $journal["fromAccount"] = $currentObjectID;
        $journal["toAccount"] = $currentObjectJournalID;
        $journal["transaction"] = Helpers::getKeyValueFromObj($object, 'transaction');
        $journal =    addEditObject($journal, JO, null);
        $journalID = Helpers::getKeyValueFromObj($journal, ID);

        Helpers::setKeyValueFromObj($object, 'isDirect', $journalID);
        Helpers::setKeyValueFromObj($journalObject, 'isDirect', $journalID);


        Helpers::unSetKeyFromObj($journalObject, JO);

        addEditObject($journalObject, $journalTable, getDefaultAddOptions());
        Helpers::unSetKeyFromObj($object, JO);
    }
    public function checkToAddRemoveJournal(&$object, $tableName)
    {
        //   echo "\n checkToAddRemoveJournal $tableName   ";
        //new record with journal
        if (Helpers::isNewRecord($object) && Helpers::isSetKeyAndNotNullFromObj($object, JO)) {
            // echo "  OBJECT IS JOURNAL $tableName  ";

            addJournalFromObject($object, $tableName);
            return;
        }
        if (!Helpers::isNewRecord($object)) {
            // 	    echo "  OBJECT IS JOURNAL isNewRecord $tableName  ";
            $OriginalRecord = depthSearch(
                Helpers::getKeyValueFromObj($object, 'iD'),
                $tableName,
                0,
                null,
                null,
                null
            );
            Helpers::setKeyValueFromObj($object, 'isDirect', null);
            if (Helpers::isSetKeyAndNotNullFromObj($OriginalRecord, JO)) {
                $this->deleteObjectThatIsJournal($OriginalRecord, $tableName);
            }
            if (Helpers::isSetKeyAndNotNullFromObj($object, JO)) {
                //	deleteObjectThatIsJournal($OriginalRecord,$tableName);	
                $this->addJournalFromObject($object, $tableName);
            }
        }
    }
    public function deleteObjectThatIsJournal($object, $tableName)
    {

        $JornalObject = Helpers::getKeyValueFromObj($object, JO);

        $AnotherTableName = $this->getJournalTableName($JornalObject);

        $AnotherTableRecord = Helpers::getKeyValueFromObj($JornalObject, $AnotherTableName)[0];

        Helpers::setKeyValueFromObj($AnotherTableRecord, "isDirect", null);

        doDelete($AnotherTableRecord, $AnotherTableName, false);
        doDelete($JornalObject, JO, false);
    }


    // public function getQueryLoginUser(bool $IsEmployee): string
    // {
    //     return "
    //             SELECT *
    //             FROM " . ($IsEmployee ? "employees" : "customers") . " 
    //             WHERE phone = :phone
    //         ";
    // }
    // private function checkToLogin($IsEmployee, string  $phone)
    // {
    //     $query = $this->getQueryLoginUser($IsEmployee);
    //     $statement = $this->database->prepare($query);
    //     // $statement->bindParam('phone', "32q3");
    //     $statement->execute(['phone' => $phone]);
    //     $user = $statement->fetch();
    //     return $user;
    // }
    // public function loginUser(string $phone, string $password)
    // {
    //     $user = $this->checkToLogin(true, $phone);

    //     if (! $user) {
    //         $user = $this->checkToLogin(false, $phone);
    //     }

    //     if (! $user) {
    //         throw new \Exception(
    //             'Login failed: phone or password incorrect.',
    //             400
    //         );
    //     }


    //     //todo on insert $hashedPassword = password_hash($password, PASSWORD_BCRYPT);



    //     if (! password_verify($password, $user["password"],)) {
    //         throw new \Exception(
    //             'Login failed: phone or password incorrect.',
    //             400
    //         );
    //     }


    //     return $user;
    // }

    // public function checkToDeleteJournal($user) {}
}
