<?php
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../Model/reclamation.php');

class ReclamationController {
    private $conn;

    public function __construct() {
        try {
            $db = new config();
            $this->conn = $db->getConnexion();
        } catch (Exception $e) {
            error_log("Erreur de connexion: " . $e->getMessage());
            throw new Exception("Erreur de connexion à la base de données");
        }
    }

    private function validateReclamation($description, $etat) {
        $errors = [];

        // Vérification de la description vide
        if (empty(trim($description))) {
            $errors[] = "La description ne peut pas être vide";
        }

        // Vérification de la longueur de la description
        if (strlen($description) > 25) {
            $errors[] = "La description ne doit pas dépasser 25 caractères";
        }

        // Vérification de l'état vide
        if (empty(trim($etat))) {
            $errors[] = "L'état ne peut pas être vide";
        }

        // Vérification de la longueur de l'état
        if (strlen($etat) > 25) {
            $errors[] = "L'état ne doit pas dépasser 25 caractères";
        }

        return $errors;
    }

    public function addReclamation($reclamation) {
        try {
            // Debug information
            error_log("Tentative d'ajout d'une réclamation:");
            error_log("Date: " . $reclamation->getDateReclamation());
            error_log("Description: " . $reclamation->getDescriptionReclamation());
            error_log("État: " . $reclamation->getEtatReclamation());

            // Validation des données
            $errors = $this->validateReclamation(
                $reclamation->  getDescriptionReclamation(), 
                $reclamation->getEtatReclamation()
            );
            
            if (!empty($errors)) {
                $_SESSION['errors'] = $errors;
                error_log("Erreurs de validation: " . implode(", ", $errors));
                return false;
            }

            $sql = "INSERT INTO reclamation (date_reclamation, description_reclamation, etat_reclamation) 
                    VALUES (:date_reclamation, :description, :etat)";
            
            $query = $this->conn->prepare($sql);
            $params = [
                'date_reclamation' => $reclamation->getDateReclamation(),
                'description' => $reclamation->getDescriptionReclamation(),
                'etat' => $reclamation->getEtatReclamation()
            ];
            
            error_log("SQL: " . $sql);
            error_log("Paramètres: " . print_r($params, true));
            
            $result = $query->execute($params);
            
            if (!$result) {
                error_log("Erreur SQL: " . print_r($query->errorInfo(), true));
                throw new Exception("Erreur lors de l'insertion dans la base de données");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Erreur PDO lors de l'ajout: " . $e->getMessage());
            $_SESSION['errors'] = ["Erreur base de données: " . $e->getMessage()];
            return false;
        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout: " . $e->getMessage());
            $_SESSION['errors'] = ["Erreur: " . $e->getMessage()];
            return false;
        }
    }

    public function getReclamations() {
        $sql = "SELECT * FROM reclamation ORDER BY date_reclamation DESC";
        try {
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reclamations: " . $e->getMessage(), 3, 'error_log.txt');
            return [];
        }
    }

    public function deleteReclamation($id) {
        $sql = "DELETE FROM reclamation WHERE id_reclamation = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error deleting reclamation: " . $e->getMessage(), 3, 'error_log.txt');
            return false;
        }
    }

    public function getReclamationById($id) {
        $sql = "SELECT * FROM reclamation WHERE id_reclamation = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reclamation by ID: " . $e->getMessage(), 3, 'error_log.txt');
            return null;
        }
    }

    public function updateReclamation($id, $date, $description, $etat) {
        // Validation des données
        $errors = $this->validateReclamation($description, $etat);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return false;
        }

        $sql = "UPDATE reclamation SET date_reclamation = :date, description_reclamation = :description, 
                etat_reclamation = :etat WHERE id_reclamation = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'date' => $date,
                'description' => $description,
                'etat' => $etat,
                'id' => $id
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Error updating reclamation: " . $e->getMessage(), 3, 'error_log.txt');
            return false;
        }
    }

    public function getRecentReclamations($limit = 5) {
        $sql = "SELECT * FROM reclamation ORDER BY date_reclamation DESC LIMIT :limit";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching recent reclamations: " . $e->getMessage(), 3, 'error_log.txt');
            return [];
        }
    }
}
?>