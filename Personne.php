<?php

abstract class Personne {
    protected string $nom;
    protected string $prenom;
    protected int $age;
    protected int $pointsVie;

    public function __construct(string $nom, string $prenom, int $age, int $pointsVie = 100) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->age = $age;
        $this->pointsVie = $pointsVie;
    }

    public function getNomComplet(): string {
        return $this->prenom . ' ' . $this->nom;
    }

    public function getAge(): int {
        return $this->age;
    }

    public function sePresenter(): string {
        return "Je suis " . $this->getNomComplet() . ", " . $this->age . " ans.";
    }

    public function vieillir(): void {
        $this->age++;
    }

    public function agir(): string {
        return $this->getNomComplet() . " demande : « Mais que voulez-vous que je fasse ? »";
    }
}
