<?php

declare(strict_types=1);

namespace App\Model;

use App\Tools\SuperGlobals;
use PDO;
use App\Tools\Database;
use App\Model\Entity\Invitation;

class InvitationModel
{

    private $bdd;
    private $superGlobals;

    public function __construct()
    {
        $this->bdd = Database::getBdd();
        $this->superGlobals = new SuperGlobals();
    }

    // CREATE NEW INVITATION
    public function createInvitation(): void
    {
        $currentUser = $this->superGlobals->_SESSION("user")['id'];
        $memberId = $this->superGlobals->_GET("memberid");
        $groupId = $this->superGlobals->_GET("groupid");

        $sql = "INSERT INTO `invitations`(`invitation_from_user_id`, `invitation_to_user_id`, `invitation_date`, `invitation_for_group_id`) VALUES (?, ?, NOW(), ?)";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$currentUser, $memberId, $groupId]);
    }

    // DELETE INVITATION
    public function deleteInvitation()
    {
        $invitationId = $this->superGlobals->_GET("invitationid");

        $sql = "DELETE FROM `invitations` WHERE `id` = ?";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$invitationId]);
    }

    public function acceptInvitation(int $userId): void
    {
        $groupId = $this->superGlobals->_GET("groupid");

        // Add User to the group in the database
        $sql = "INSERT INTO `group_members`(`group_id`, `user_id`) VALUES (?, ?)";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$groupId, $userId]);

        // Delete invitation
        $invitationId = $this->superGlobals->_GET("invitationid");
        $sql = "DELETE FROM `invitations` WHERE `id` = ?";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$invitationId]);
    }

    public function getInvitationsFromMe(): array
    {
        $currentUserId = (int)$this->superGlobals->_SESSION("user")['id'];

        $sql = "SELECT * FROM `invitations` WHERE `invitation_from_user_id` = ?";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$currentUserId]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, Invitation::class);
    }

    public function getInvitationsForMe(): array
    {
        $currentUserId = (int)$this->superGlobals->_SESSION("user")['id'];

        $sql = "SELECT * FROM `invitations` WHERE `invitation_to_user_id` = ?";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$currentUserId]);

        return $stmt->fetchAll(PDO::FETCH_CLASS, Invitation::class);
    }

    public function getUserCount(int $groupId, int $userId): int
    {
        $sql = "SELECT * FROM `group_members` WHERE `group_id` = ? AND `user_id` = ?";
        $stmt = $this->bdd->prepare($sql);
        $stmt->execute([$groupId, $userId]);

        return $stmt->rowCount();
    }

}
