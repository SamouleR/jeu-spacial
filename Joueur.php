<?php
require_once 'Vaisseau.php';
require_once 'Drone.php';

class Joueur {
    public int $id;
    public Vaisseau $vaisseau;
    /** @var Drone[] */
    public array $dronesIndependants = [];

    // --- MODIFICATION : Stock initial à 3 ---
    public int $nbDrones = 3; 

    public int $bouclierPoints = 0;      
    public int $malusTirTours = 0;       

    public function __construct(int $id, Vaisseau $vaisseau) {
        $this->id = $id;
        $this->vaisseau = $vaisseau;
    }

    public function getDronesData(): array {
        $data = [];
        foreach ($this->dronesIndependants as $drone) {
            $data[] = [
                'type'     => $drone->getType(),
                'energie'  => $drone->getEnergie(),
                'position' => $drone->getPosition()
            ];
        }
        return $data;
    }
}
?>