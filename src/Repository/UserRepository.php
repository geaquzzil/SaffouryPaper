<?php



namespace Etq\Restful\Repository;


use Etq\Restful\Repository\BaseRepository;
use Exception;

class UserRepository extends BaseRepository
{
    public function getQueryLoginUser(): string
    {
        return "
               SELECT
                     iD,phone , password, userlevelid from employees WHERE phone = :phone 
                UNION all 
                SELECT
                    iD,phone , password, userlevelid from customers WHERE phone = :phone
            ";
    }

    public function block(?string $tableName, bool $block, ?int $iD = null, ?Options $options = null)
    {

        $val = $block ? "'0'" : "'1'";
        $query = [];
        $isCountable = $tableName ? false : true;

        $iD = $iD ? "WHERE iD ='$iD'" : "";
        if ($tableName) {

            $query[] = "UPDATE $tableName SET " . ACTIVATION_FIELD . "=$val  $iD";
        } else {
            $query[] = "UPDATE " . EMP . " SET " . ACTIVATION_FIELD . "=$val WHERE " . KLVL . "` <> ' " . ADMIN_ID . "'";
            $query[] = "UPDATE " . CUST . " SET " . ACTIVATION_FIELD . "=$val";
        }

        $customerCount = 0;
        $empCount = 0;
        for ($i = 0; $i < count($query); $i++) {
            if ($isCountable) {
                if ($i == 0) {
                    $empCount = $this->getUpdateTableWithQuery($query[$i]);
                } else {
                    $customerCount = $this->getUpdateTableWithQuery($query[$i]);
                }
            } else {
                $empCount = $customerCount = $this->getUpdateTableWithQuery($query[$i]);
            }
        }
        $response = array();
        if ($isCountable) {
            $response[EMP] = $empCount;
            $response[CUST] = $customerCount;
        } else {
            $response[$tableName] = $empCount;
        }

        $response["serverStatus"] = true;
        return $response;
    }

    public function updateToken(string $token, Options $options)
    {
        $tableName = $options->auth->isEmployee() ? EMP : CUST;
        $iD = $options->auth->getUserID();
        return $this->edit(
            $tableName,
            $iD,
            [
                "token" => $token
            ],
            $options,
            false
        );
    }

    private function checkToLogin(string  $phone)
    {
        $query = $this->getQueryLoginUser();
        $statement = $this->database->prepare($query);
        // $statement->bindParam('phone', "32q3");
        $statement->execute(['phone' => $phone]);
        $user = $statement->fetch();
        return $user;
    }
    public function loginUser(string $phone, string $password)
    {
        $user = $this->checkToLogin($phone);
        if (! $user) {
            throw new \Exception(
                'Login failed: phone or password incorrect.',
                400
            );
        }


        //todo on insert $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        // echo password_hash($password, PASSWORD_BCRYPT);



        if (! password_verify($password, $user["password"])) {
            throw new \Exception(
                'Login failed: phone or password incorrect.',
                400
            );
        }


        return $user;
    }
}
