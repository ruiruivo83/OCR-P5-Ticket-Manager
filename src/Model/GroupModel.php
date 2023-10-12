<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\Group;
use App\Tools\SuperGlobals;
use PDO;
use App\Tools\Database;

class GroupModel
{
    private $bdd;
    private $superGlobals;

    public function __construct()
    {
        $this->bdd = Database::getBdd();
        $this->superGlobals = new SuperGlobals();
    }

    public function getMyGroups(): array
    {
        $currentUser = $this->superGlobals->_SESSION("user")['id'];
        $stmt = $this->bdd->prepare("SELECT * FROM `groups` WHERE `group_admin_id` = :userId ORDER BY `creation_date` DESC");
        $stmt->bindParam(':userId', $currentUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, Group::class);
    }

    public function getSharedGroupsAndDetails(): array
    {
        $currentUserId = (int)$this->superGlobals->_SESSION("user")['id'];
        $stmt = $this->bdd->prepare("SELECT * FROM `group_members` `grpmembers` INNER JOIN `groups` `grp` ON `grp`.`id` = `grpmembers`.`group_id` INNER JOIN `users` `usr` ON `usr`.`id` = `grp`.`group_admin_id` WHERE `grpmembers`.`user_id` = :userId AND `grp`.`group_status` = 'open'");
        $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getGroupDetails(int $groupId): Group
    {
        $stmt = $this->bdd->prepare("SELECT * FROM `groups` WHERE `id` = :groupId ORDER BY `creation_date` DESC");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, Group::class);
        return $stmt->fetch();
    }

    public function getGroupMembersCount(int $groupId): int
    {
        $stmt = $this->bdd->prepare("SELECT * FROM `group_members` WHERE `group_id` = :groupId");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function testGroupMemberForCurrentUser(int $groupId): int
    {
        $currentUser = $this->superGlobals->_SESSION("user")['id'];
        $stmt = $this->bdd->prepare("SELECT * FROM `group_members` WHERE `group_id` = :groupId AND `user_id` = :userId");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $currentUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function testGroupAdminForCurrentUser(int $groupId): int
    {
        $currentUser = $this->superGlobals->_SESSION("user")['id'];
        $stmt = $this->bdd->prepare("SELECT * FROM `groups` WHERE `id` = :groupId AND `group_admin_id` = :userId");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $currentUser, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function removeMemberFromGroupfunction(int $groupId, int $userId): void
    {
        $stmt = $this->bdd->prepare("DELETE FROM `group_members` WHERE `group_id` = :groupId AND `user_id` = :userId");
        $stmt->bindParam(':groupId', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function createNewGroup()
    {
        $currentUser = $_SESSION['user']['id'];
        $stmt = $this->bdd->prepare("INSERT INTO `groups` (`group_admin_id`, `creation_date`, `group_name`, `group_description`, `group_status`) VALUES (:userId, NOW(), :title, :description, 'open')");
        $stmt->bindParam(':userId', $currentUser, PDO::PARAM_INT);
        $stmt->bindParam(':title', $this->superGlobals->_POST("Title"), PDO::PARAM_STR);
        $stmt->bindParam(':description', $this->superGlobals->_POST("Description"), PDO::PARAM_STR);
        $stmt->execute();
    }
}
