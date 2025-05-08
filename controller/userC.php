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
            $query = $db->query($sql);
            return $query->fetchAll(PDO::FETCH_ASSOC);
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

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM user WHERE email = :email";
        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }

    // Vérifier si un email existe
    public function emailExists($email) {
        $sql = "SELECT * FROM users WHERE email = :email";
        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->execute(['email' => $email]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error checking email: " . $e->getMessage());
            return false;
        }
    }

    // Sauvegarder le token de réinitialisation
    public function saveResetToken($email, $token, $expiry) {
        $sql = "UPDATE users SET reset_token = :token, reset_token_expiry = :expiry WHERE email = :email";
        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            return $query->execute([
                'token' => $token,
                'expiry' => $expiry,
                'email' => $email
            ]);
        } catch (Exception $e) {
            error_log("Error saving reset token: " . $e->getMessage());
            return false;
        }
    }

    // Vérifier si un token est valide et non expiré
    public function checkResetToken($token) {
        $sql = "SELECT * FROM users WHERE reset_token = :token AND reset_token_expiry > NOW()";
        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->execute(['token' => $token]);
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error checking reset token: " . $e->getMessage());
            return false;
        }
    }

    // Réinitialiser le mot de passe
    public function resetPassword($token, $newPassword) {
        $sql = "UPDATE users SET password = :password, reset_token = NULL, reset_token_expiry = NULL 
                WHERE reset_token = :token AND reset_token_expiry > NOW()";
        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            return $query->execute([
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'token' => $token
            ]);
        } catch (Exception $e) {
            error_log("Error resetting password: " . $e->getMessage());
            return false;
        }
    }

    // Mettre à jour le mot de passe
    public function updatePassword($email, $newPassword) {
        $sql = "UPDATE user SET mdp = :password WHERE email = :email";
        try {
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            return $query->execute([
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'email' => $email
            ]);
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }
}
?>
