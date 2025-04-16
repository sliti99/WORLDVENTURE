<?php

class Reclamation
{
    private $id_reclamation;
    private $date_reclamation;
    private $description_reclamation;
    private $etat_reclamation;

    // Constructeur
    public function __construct($date_reclamation, $description_reclamation, $etat_reclamation)
    {
        $this->date_reclamation = $date_reclamation;
        $this->description_reclamation = $description_reclamation;
        $this->etat_reclamation = $etat_reclamation;
    }

    // Getters et Setters
    public function getIdReclamation()
    {    }

    public function setIdReclamation($id_reclamation)
    {
        $this->id_reclamation = $id_reclamation;
    }

    public function getDateReclamation()
    {
        return $this->date_reclamation;
    }

    public function setDateReclamation($date_reclamation)
    {
        $this->date_reclamation = $date_reclamation;
    }

    public function getDescriptionReclamation()
    {
        return $this->description_reclamation;
    }

    public function setDescriptionReclamation($description_reclamation)
    {
        $this->description_reclamation = $description_reclamation;
    }

    public function getEtatReclamation()
    {
        return $this->etat_reclamation;
    }

    public function setEtatReclamation($etat_reclamation)
    {
        $this->etat_reclamation = $etat_reclamation;
    }
}
?>