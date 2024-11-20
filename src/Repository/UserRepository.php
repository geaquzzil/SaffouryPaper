<?php



namespace Etq\Restful\Repository;


use Etq\Restful\Repository\BaseRepository;



class UserRepository extends BaseRepository
{

    public function getQueryLoginUser(bool $IsEmployee): string
    {
        return "
                SELECT *
                FROM " . ($IsEmployee ? "employees" : "customers") . " 
                WHERE phone = :phone
            ";
    }
    private function checkToLogin($IsEmployee, string  $phone)
    {
        $query = $this->getQueryLoginUser($IsEmployee);
        $statement = $this->database->prepare($query);
        // $statement->bindParam('phone', "32q3");
        $statement->execute(['phone' => $phone]);
        $user = $statement->fetch();
        return $user;
    }
    public function loginUser(string $phone, string $password)
    {
        $user = $this->checkToLogin(true, $phone);

        if (! $user) {
            $user = $this->checkToLogin(false, $phone);
        }

        if (! $user) {
            throw new \Exception(
                'Login failed: phone or password incorrect.',
                400
            );
        }


        //todo on insert $hashedPassword = password_hash($password, PASSWORD_BCRYPT);



        if (! password_verify($password, $user["password"],)) {
            throw new \Exception(
                'Login failed: phone or password incorrect.',
                400
            );
        }


        return $user;
    }

}
