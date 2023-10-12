<?php

declare(strict_types=1);

namespace App\Model;

use App\Model\Entity\Group;
use App\Tools\SuperGlobals;
use PDO;
use App\Tools\Database;
use App\Model\Entity\Ticket;

class TicketModel
{

    private $bdd;
    private $superGlobals;

    public function __construct()
    {
        $this->bdd = Database::getBdd();
        $this->superGlobals = new SuperGlobals();
    }

    public function createNewTicket(): void
    {
        $currentUser = $this->superGlobals->_SESSION("user")['id'];
        $sql = "INSERT INTO `tickets` (`author_id`, `requester`, `status`, `creation_date`, `title`, `description`, `group_id`) VALUES (?, ?, ?, NOW(), ?, ?, ?)";
        $req = $this->bdd->prepare($sql);
        $req->execute([$currentUser, $this->superGlobals->_POST("Requester"), "open", $this->superGlobals->_POST("Title"), $this->superGlobals->_POST("Description"), $this->superGlobals->_GET("groupid")]);
    }

    public function getTicketDetails(int $id): Ticket
    {
        $sql = "SELECT * FROM `tickets` WHERE `id` = ? ORDER BY `creation_date` DESC";
        $req = $this->bdd->prepare($sql);
        $req->execute([$id]);
        $req->setFetchMode(PDO::FETCH_CLASS, Ticket::class);
        return $req->fetch();
    }

    public function getTicketGroupIdWithTicketId($ticketId): Ticket
    {
        $sql = "SELECT `group_id` FROM `tickets` WHERE `id` = ?";
        $req = $this->bdd->prepare($sql);
        $req->execute([$ticketId]);
        $req->setFetchMode(PDO::FETCH_CLASS, Ticket::class);
        return $req->fetch();
    }

    // GET OPEN TICKET WITH GROUP ID
    public function getOpenTicketsWithGroupId(int $groupId): array
    {
        $sql = "SELECT * FROM `tickets` WHERE `group_id` = ? AND `status` = 'open' ORDER BY `creation_date` DESC";
        $req = $this->bdd->prepare($sql);
        $req->execute([$groupId]);
        return $req->fetchAll();
    }

    // GET CLOSED TICKET WITH GROUP ID
    public function getClosedTicketsWithGroupId(int $groupId): array
    {
        $sql = "SELECT * FROM `tickets` WHERE `group_id` = ? AND `status` = 'closed' ORDER BY `creation_date` DESC";
        $req = $this->bdd->prepare($sql);
        $req->execute([$groupId]);
        return $req->fetchAll();
    }

    // CLOSE TICKET
    public function closeTicket(): void
    {
        $status = "closed";
        $sql = "UPDATE `tickets` SET `status` = ?, `ticket_status_change_date` = NOW() WHERE `id` = ?";
        $req = $this->bdd->prepare($sql);
        $req->execute([$status, $this->superGlobals->_GET("ticketid")]);
    }

    // STATS - GET ALL OPEN TICKETS THIS MONTH
    public function getMyTickets(string $status): array
    {
        $currentUserId = $this->superGlobals->_SESSION("user")['id'];
        $sql = "SELECT * FROM `tickets` WHERE `author_id` = ? AND `status` = ? ORDER BY `creation_date` DESC";
        $req = $this->bdd->prepare($sql);
        $req->execute([$currentUserId, $status]);
        return $req->fetchAll();
    }

    // STATS - GET ALL OPEN TICKETS THIS MONTH
    public function getMyTicketsForYearAndMonth($CreationYear, $CreationMonth, $status)
    {
        $currentUserId = $this->superGlobals->_SESSION("user")['id'];
        $sql = "SELECT `creation_date` FROM `tickets` WHERE YEAR(`creation_date`) = ? AND MONTH(`creation_date`) = ? AND `status` = ? AND `author_id` = ? ORDER BY `creation_date` DESC";
        $req = $this->bdd->prepare($sql);
        $req->execute([$CreationYear, $CreationMonth, $status, $currentUserId]);
        return $req->fetchAll();
    }
}
