<?php

use Etq\Restful\Repository\BaseRepository;

class User extends BaseRepository
{

    public function getQueryLoginUser(bool $IsEmployee): string
    {
        return "
                SELECT *
                FROM " . ($IsEmployee ? `employee` : `customer`) . "
                WHERE `phone` = :phone
                ORDER BY `id`
            ";
    }
    private function checkToLogin($IsEmployee)
    {
        $query = $this->getQueryLoginUser($IsEmployee);
        $statement = $this->database->prepare($query);
        $statement->bindParam('email', $email);
        $statement->execute();
        $user = $statement->fetch();
        return $user;
    }
    public function login(string $email, string $password)
    {
        $user = $this->checkToLogin(true);

        if (! $user) {
            $user = $this->checkToLogin(false);
        }

        if (! $user) {
            throw new \Exception(
                'Login failed: Email or password incorrect.',
                400
            );
        }

        if (! password_verify($password, $user->getPassword())) {
            throw new \Exception(
                'Login failed: Email or password incorrect.',
                400
            );
        }
        

        return $user;
    }

    public function checkPermission($user){


    }
}
