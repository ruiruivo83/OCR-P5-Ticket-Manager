<?php

declare(strict_types=1);

namespace App\Model;

use PDO;
use App\Tools\Database;
use App\Model\Entity\Member;

class MemberModel
{
    private $bdd;

    public function __construct()
    {
        $this->bdd = Database::getBdd();
    }

    public function getGroupMembersAndDetails(int $groupId): array
    {
        $sql = "SELECT * FROM `group_members` AS `grp` INNER JOIN `users` AS `usr` ON `usr`.`id` = `grp`.`user_id` WHERE `group_id` = ?";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$groupId]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, Member::class);
    }
}
