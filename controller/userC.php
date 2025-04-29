<?php
require_once('config.php');

class UserC
{
    // Ajouter un utilisateur
    public function addUser($user)
    {
        $sql = "INSERT INTO user (nom, email, mdp, tel, ville, daten, role)
                VALUES (:nom, :email, :mdp, :tel, :ville, :daten, :role)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute([
                'nom' => $user->getNom(),
                'email' => $user->getEmail(),
                'mdp' => $user->getMdp(),
                'tel' => $user->getTel(),
                'ville' => $user->getVille(),
                'daten' => $user->getDaten(),
                'role' => $user->getRole()

            ]);
            return "Utilisateur ajouté avec succès !";
        } catch (PDOException $e) {
            echo 'Erreur PDO : ' . $e->getMessage();
            return "Erreur lors de l'ajout de l'utilisateur.";
        }
    }

    // Liste des utilisateurs
    public function listUsers()
    {
        $sql = "SELECT * FROM user";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    // Supprimer un utilisateur
    public function deleteUser($id)
    {
        $sql = "DELETE FROM user WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id);
            $result = $query->execute();
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    // Afficher un utilisateur
    public function showUser($id)
    {
        $sql = "SELECT * FROM user WHERE id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->execute();
            $user = $query->fetch();
            return $user;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'affichage de l\'utilisateur : ' . $e->getMessage());
        }
    }

    // Mettre à jour un utilisateur
    public function updateUser($id, $nom, $email, $mdp, $tel, $ville, $daten)
    {
        $sql = "UPDATE user SET
                    nom = :nom,
                    email = :email,
                    mdp = :mdp,
                    tel = :tel,
                    ville = :ville,
                    daten = :daten
                WHERE id = :id";

        $db = config::getConnexion();
        try {
            // Si le mot de passe est fourni et n'est pas déjà hashé, le hasher
            if ($mdp && !str_starts_with($mdp, '$2y$')) {
                $mdp = password_hash($mdp, PASSWORD_BCRYPT);
            }
            
            $query = $db->prepare($sql);
            $query->bindValue(':nom', $nom);
            $query->bindValue(':email', $email);
            $query->bindValue(':mdp', $mdp);
            $query->bindValue(':tel', $tel);
            $query->bindValue(':ville', $ville);
            $query->bindValue(':daten', $daten);
            $query->bindValue(':id', $id);

            return $query->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function verifyLogin($email, $password) {
        $sql = "SELECT * FROM user WHERE email = :email";
        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            
            $params = ['email' => $email];
            $query->execute($params);
            
            $user = $query->fetch();
            
            if ($user && password_verify($password, $user['mdp'])) {
                return $user;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
