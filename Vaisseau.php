<?php

class Vaisseau {
    private string $nom;
    private int $vie;
    private int $energie;
    private int $puissanceTir;
    private array $position;

    // --- CONFIGURATION DES COÛTS ---
    const COUT_PAR_CASE = 2;    // Coût par case déplacée (ex: 3 cases = 6 énergie)
    const COUT_TIR = 10;        // Tir coûte 10
    const COUT_DRONE = 30;      // Lancer un drone coûte 30

    public function __construct(string $nom, int $vieMax, int $energieMax, int $puissanceTir, array $position = ['x' => 1, 'y' => 1]) {
        $this->nom = $nom;
        $this->vie = $vieMax;
        $this->energie = $energieMax;
        $this->puissanceTir = $puissanceTir;
        $this->position = $position;
    }

    // --- Getters ---
    public function getNom(): string { return $this->nom; }
    public function getVie(): int { return $this->vie; }
    public function getEnergie(): int { return $this->energie; }
    public function getPosition(): array { return $this->position; }
    public function getPuissanceTir(): int { return $this->puissanceTir; }

    // --- Gestion de la Vie ---
    public function estDetruit(): bool {
        return $this->vie <= 0;
    }

    public function subirDegats(int $degats): void {
        $this->vie -= $degats;
        if ($this->vie < 0) $this->vie = 0;
    }

    public function soigner(int $points): void {
        $this->vie += $points;
        if ($this->vie > 100) $this->vie = 100;
    }

    // --- Gestion de l'Énergie ---
    public function regenererEnergie(int $montant = 10): void {
        $this->energie += $montant;
        if ($this->energie > 100) $this->energie = 100;
    }

    // --- Actions ---

    public function deplacer(int $x, int $y): bool {
        if ($this->estDetruit()) return false;
        
        // Vérification des limites du plateau (1-10)
        if ($x < 1 || $x > 10 || $y < 1 || $y > 10) return false;

        // 1. Calcul de la distance (Distance de Manhattan : |x1-x2| + |y1-y2|)
        $distance = abs($x - $this->position['x']) + abs($y - $this->position['y']);

        // 2. Calcul du coût variable selon la distance
        $coutTotal = $distance * self::COUT_PAR_CASE;

        // 3. Vérification si assez d'énergie
        if ($this->energie < $coutTotal) {
            return false; // Pas assez d'énergie pour cette distance
        }

        // 4. Application
        $this->energie -= $coutTotal;
        $this->position = ['x' => $x, 'y' => $y];
        return true;
    }

    public function tirer(Vaisseau $cible): int {
        if ($this->estDetruit()) return 0;

        if ($this->energie < self::COUT_TIR) {
            return -1; 
        }

        $this->energie -= self::COUT_TIR;
        
        $degats = $this->puissanceTir;
        $cible->subirDegats($degats);
        return $degats;
    }

    public function lancerDrone(): bool {
        if ($this->energie >= self::COUT_DRONE) {
            $this->energie -= self::COUT_DRONE;
            return true;
        }
        return false;
    }
}
?>