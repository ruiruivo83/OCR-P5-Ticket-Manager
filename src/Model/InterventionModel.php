<?php

declare(strict_types=1);

namespace App\Model;

use App\Tools\SuperGlobals;
use PDO;
use App\Tools\Database;
use App\Model\Entity\Intervention;

class InterventionModel
{
    private $bdd;
    private $superGlobals;

    public function __construct()
    {
        $this->bdd = Database::getBdd();
        $this->superGlobals = new SuperGlobals();
    }

    public function getInterventionForTicketId(int $id): array
    {
        $stmt = $this->bdd->prepare("SELECT * FROM `ticket_interventions` WHERE `ticket_id` = :ticketId ORDER BY `intervention_date` DESC");
        $stmt->bindParam(':ticketId', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, Intervention::class);
    }

    public function getInterventionsForTicketIdAndAuthorDetails(int $ticketId): array
    {
        $stmt = $this->bdd->prepare("SELECT * FROM `ticket_interventions` `tktinter` INNER JOIN `users` `usr` ON `usr`.`id` = `tktinter`.`intervention_author_id` WHERE `tktinter`.`ticket_id` = :ticketId ORDER BY `intervention_date` DESC");
        $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, Intervention::class);
    }

    public function createNewIntervention(): void
    {
        $stmt = $this->bdd->prepare("INSERT INTO `ticket_interventions` (`ticket_id`, `intervention_author_id`, `intervention_date`, `intervention_description`, `intervention_author_country`, `intervention_author_company`) VALUES (:ticketId, :authorId, NOW(), :description, :authorCountry, :authorCompany)");

        $ticketid = $this->superGlobals->_POST("ticketid");
        $stmt->bindParam(':ticketId', $ticketid, PDO::PARAM_INT);
        $stmt->bindParam(':authorId', $this->superGlobals->_SESSION("user")['id'], PDO::PARAM_INT);

        $description = $this->superGlobals->_POST("Description");
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':authorCountry', $this->superGlobals->_SESSION("user")['country'], PDO::PARAM_STR);
        $stmt->bindParam(':authorCompany', $this->superGlobals->_SESSION("user")['company'], PDO::PARAM_STR);
        $stmt->execute();
    }

    public function createClosingIntervention($ticketId, $interventionDescription): void
    {
        $stmt = $this->bdd->prepare("INSERT INTO `ticket_interventions` (`ticket_id`, `intervention_author_id`, `intervention_date`, `intervention_description`, `intervention_author_country`, `intervention_author_company`) VALUES (:ticketId, :authorId, NOW(), :description, :authorCountry, :authorCompany)");
        $stmt->bindParam(':ticketId', $ticketId, PDO::PARAM_INT);
        $stmt->bindParam(':authorId', $this->superGlobals->_SESSION("user")['id'], PDO::PARAM_INT);
        $stmt->bindParam(':description', $interventionDescription, PDO::PARAM_STR);
        $stmt->bindParam(':authorCountry', $this->superGlobals->_SESSION("user")['country'], PDO::PARAM_STR);
        $stmt->bindParam(':authorCompany', $this->superGlobals->_SESSION("user")['company'], PDO::PARAM_STR);
        $stmt->execute();
    }

public function getMyInterventionsForYearAndMonth($CreationYear, $CreationMonth)
{
    $currentUserId = $this->superGlobals->_SESSION("user")['id'];
    $year = $CreationYear; // Assign to a variable
    $month = $CreationMonth; // Assign to a variable
    
    $stmt = $this->bdd->prepare("SELECT `intervention_date` FROM `ticket_interventions` WHERE YEAR(`intervention_date`) = :year AND MONTH(`intervention_date`) = :month AND `intervention_author_id` = :userId ORDER BY `intervention_date` DESC");
    $stmt->bindParam(':year', $year, PDO::PARAM_STR); // Use the variables
    $stmt->bindParam(':month', $month, PDO::PARAM_STR);
    $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll();
}


    public function getMyInterventions(): array
    {
        $currentUserId = $this->superGlobals->_SESSION("user")['id'];
        $stmt = $this->bdd->prepare("SELECT * FROM `ticket_interventions` WHERE `intervention_author_id` = :userId ORDER BY `intervention_date` DESC");
        $stmt->bindParam(':userId', $currentUserId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
                return $stmt->fetchAll();
    }
}

   
