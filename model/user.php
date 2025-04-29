<?php
class User
{
    private $id;
    private $nom;
    private $email;
    private $mdp;
    private $tel;
    private $ville;
    private $daten;
    private $role;

    // Constructor avec paramètres optionnels
    public function __construct(
        $id = null,
        $nom = null,
        $email = null,
        $mdp = null,
        $tel = null,
        $ville = null,
        $daten = null,
        $role = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->email = $email;
        $this->mdp = $mdp;
        $this->tel = $tel;
        $this->ville = $ville;
        $this->daten = $daten;
        $this->role = $role;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getEmail() { return $this->email; }
    public function getMdp() { return $this->mdp; }
    public function getTel() { return $this->tel; }
    public function getVille() { return $this->ville; }
    public function getDaten() { return $this->daten; }
    public function getRole() { return $this->role; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMdp($mdp) { $this->mdp = $mdp; }
    public function setTel($tel) { $this->tel = $tel; }
    public function setVille($ville) { $this->ville = $ville; }
    public function setDaten($daten) { $this->daten = $daten; }
    public function setRole($role) { $this->role = $role; }
}
?>
