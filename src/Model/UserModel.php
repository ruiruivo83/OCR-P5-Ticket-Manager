<?php

declare(strict_types=1);

namespace App\Model;

use App\Tools\SuperGlobals;
use PDO;
use App\Tools\Database;
use App\Model\Entity\User;

class UserModel
{

    private $bdd;
    private $superGlobals;

    public function __construct()
    {
        $this->bdd = Database::getBdd();
        $this->superGlobals = new SuperGlobals();
    }

    // CREATE NEW USER
    public function createNewUser()
    {
        $sql = "INSERT INTO `users` (`firstname`, `lastname`, `email`, `psw`, `creation_date`, `country`) VALUES (?, ?, ?, ?, NOW(), ?)";
        $req = $this->bdd->prepare($sql);
        $req->execute([
            $this->superGlobals->_POST("firstname"),
            $this->superGlobals->_POST("lastname"),
            $this->superGlobals->_POST("email"),
            password_hash($this->superGlobals->_POST("psw"), PASSWORD_DEFAULT),
            $this->superGlobals->_POST("country")
        ]);
    }

    // SEARCH USERS
    public function searchUsers(string $searchText): array
    {
        $sql = "SELECT * FROM `users` WHERE (LOWER(`firstname`) LIKE LOWER(?)) OR (LOWER(`lastname`) LIKE LOWER(?)) OR (LOWER(`email`) LIKE LOWER(?) OR `id` LIKE ?)";
        $req = $this->bdd->prepare($sql);
        $req->execute(["%" . $searchText . "%", "%" . $searchText . "%", "%" . $searchText . "%", "%" . $searchText . "%"]);
        return $req->fetchAll(PDO::FETCH_CLASS, User::class);
    }

    // FIND USER BY EMAIL
    // MUST NOT DECLARE A RETURN - SOLUTION: create testExistenceUserByEmail -> return bool
    public function getUserByEmail(string $email): array
    {
        $sql = "SELECT * FROM `users` WHERE `email` = ?";
        $req = $this->bdd->prepare($sql);
        $req->execute([$email]);
        $req->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $req->fetch();
    }

    // FIND USER BY EMAIL
    public function getUserById(int $id): array
    {
        $sql = "SELECT * FROM `users` WHERE `id` = ?";
        $req = $this->bdd->prepare($sql);
        $req->execute([$id]);
        $req->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $req->fetch();
    }

    // VERIFY IF USER EMAIL EXISTS IN THE DATABASE
    public function getEmailCount($email)
    {
        $sql = "SELECT * FROM `users` WHERE `email` = ?";
        $req = $this->bdd->prepare($sql);
        $req->execute([$email]);
        return $req->rowCount();
    }

    // ATTACH PHOTO FILE NAME TO USER
    public function attachPhotoFileNameToUser(int $userId, string $fileName): void
    {
        $sql = "UPDATE `users` SET `photo_filename` = ? WHERE `id` = ?";
        $req = $this->bdd->prepare($sql);
        $req->execute([$fileName, $userId]);
    }

    // SAVE COUNTRY AND COMPANY IN USER PROFILE
    public function saveCompanyAndCountryFunction(string $country, string $company, int $userId): void
    {
        $sql = "UPDATE `users` SET `country` = ?, `company` = ? WHERE `id` = ?";
        $req = $this->bdd->prepare($sql);
        $req->execute([$country, $company, $userId]);
    }
}
