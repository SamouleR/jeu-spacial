<?php

class Drone {
    private string $type;      // 'attaque', 'bouclier', 'sabotage', 'reparation', 'kamikaze'
    private int $energie;
    private int $portee;
    private array $position;   // ['x' => int, 'y' => int]

    public function __construct(string $type, int $energie, int $portee, array $position) {
        $this->type = $type;
        $this->energie = $energie;
        $this->toursRestants = 1;
        $this->portee = $portee;
        $this->position = $position;
    }
    
    private int $toursRestants;

public function setToursRestants(int $val) {
    $this->toursRestants = $val;
}

public function getToursRestants(): int {
    return $this->toursRestants;
}


    public function getType(): string {
        return $this->type;
    }

    public function getEnergie(): int {
        return $this->energie;
    }

    public function getPosition(): array {
        return $this->position;
    }

    public function consommer(int $valeur = 10): void {
        $this->energie -= $valeur;
        if ($this->energie < 0) {
            $this->energie = 0;
        }
    }
}
