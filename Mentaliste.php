<?php
require_once 'Personne.php';

class Mentaliste extends Personne {
    private int $mana;

    public function __construct(string $nom, string $prenom, int $age, int $mana = 100) {
        parent::__construct($nom, $prenom, $age);
        $this->mana = $mana;
    }

    public function sePresenter(): string {
        return "Je suis un mentaliste de {$this->age} ans, il me reste {$this->mana} de mana.";
    }

    public function getMana(): int {
        return $this->mana;
    }

    public function agirSur(Personne $cible): string {
        if ($this->mana < 20) {
            return $this->getNomComplet() . " n'a plus assez de mana pour influencer.";
        }

        $this->mana -= 20;
        $resultat = $cible->agir();

        return $this->getNomComplet() . " influence " . $cible->getNomComplet() . " : " . $resultat . " (mana restante : {$this->mana})";
    }
}
