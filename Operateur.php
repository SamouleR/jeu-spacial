<?php
require_once 'Personne.php';

class Operateur extends Personne {
    private string $metier;
    private int $experience;

    public function __construct(string $nom, string $prenom, int $age, string $metier, int $experience = 0) {
        parent::__construct($nom, $prenom, $age);
        $this->metier = $metier;
        $this->experience = $experience;
    }

    public function sePresenter(): string {
        return "Je suis un opérateur {$this->metier} de {$this->age} ans, expérience {$this->experience}.";
    }

    public function agir(): string {
        $action = '';

        switch (strtolower($this->metier)) {
            case 'pilote':
                $action = "Le vaisseau est en train de décoller.";
                break;
            case 'entretien':
                $action = "Le vaisseau est en train d'être nettoyé.";
                break;
            case 'maintenance':
                $action = "Les systèmes du vaisseau sont en cours de réparation.";
                break;
            default:
                $action = "L'opérateur {$this->metier} effectue sa tâche.";
        }

        $this->experience += 5;

        return $this->getNomComplet() . " : " . $action . " (expérience : {$this->experience})";
    }
}
