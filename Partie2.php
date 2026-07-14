<?php
require_once 'Joueur.php';

class Partie {
    public int $id;
    public Joueur $joueur1;
    public Joueur $joueur2;
    public int $joueurActifId = 1;
    public int $tourCourant = 1;
    public string $statut = 'en_cours'; // en_cours | finie
    
    
    private function calculerDistance(array $posA, array $posB): int {
        return abs($posA['x'] - $posB['x']) + abs($posA['y'] - $posB['y']);
    }

    public function __construct(int $id) {
        $this->id = $id;

        $vaisseau1 = new Vaisseau("Ajax Star", 100, 20, ['x' => 1,  'y' => 1]);
        $vaisseau2 = new Vaisseau("PHP Fighter", 100, 20, ['x' => 10, 'y' => 10]);

        $this->joueur1 = new Joueur(1, $vaisseau1);
        $this->joueur2 = new Joueur(2, $vaisseau2);
    }
    
    private function nettoyerDrones(Joueur $joueur) {
        $joueur->dronesIndependants = array_filter(
            $joueur->dronesIndependants,
            function($drone) {
                return $drone->getEnergie() > 0;
            }
        );
    }
    
    private function tickDrones(Joueur $joueur): void {
        
        
        foreach ($joueur->dronesIndependants as $drone) {
            $drone->consommer(10);
            
            
            if ($drone->getType() === 'attaque') {

        $adversaire = $this->getAdversaire();
        $distance = $this->calculerDistance($drone->getPosition(), $adversaire->vaisseau->getPosition());

        if ($distance <= 3) {
            // Dégât de zone automatique
            $adversaire->vaisseau->subirDegats(10);
        }
    }
        }
    }

    public function getJoueurActif(): Joueur {
        return $this->joueurActifId === 1 ? $this->joueur1 : $this->joueur2;
    }

    public function getAdversaire(): Joueur {
        return $this->joueurActifId === 1 ? $this->joueur2 : $this->joueur1;
    }

    public function terminerTour(): void {
        if ($this->joueurActifId === 1) {
            if ($this->joueur1->malusTirTours > 0) {
                $this->joueur1->malusTirTours--;
            }
        } else {
            if ($this->joueur2->malusTirTours > 0) {
                $this->joueur2->malusTirTours--;
            }
        }

        $this->joueurActifId = $this->joueurActifId === 1 ? 2 : 1;
        $this->tourCourant++;

        $this->tickDrones($this->joueur1);
        $this->tickDrones($this->joueur2);

        $this->nettoyerDrones($this->joueur1);
        $this->nettoyerDrones($this->joueur2);
    }

    public function verifierFinDePartie(): void {
        if ($this->joueur1->vaisseau->estDetruit() || $this->joueur2->vaisseau->estDetruit()) {
            $this->statut = 'finie';
        }
    }

    public function executerAction(array $actionData, int $joueurId): array {
        $joueurActif = $this->getJoueurActif();
        $adversaire  = $this->getAdversaire();
        $message = "Action effectuée.";
        $succes  = true;

        if ($joueurId !== $this->joueurActifId && !in_array($actionData['type_action'] ?? '', ['initialiser', 'get_status'])) {
            return ['succes' => false, 'message' => "Ce n'est pas votre tour !"];
        }

        $type = $actionData['type_action'] ?? 'get_status';

        switch ($type) {
            case 'initialiser':
                $message = "Partie initialisée.";
                break;

            case 'get_status':
                $message = "État de la partie actualisé.";
                break;

            case 'deplacer':
                if (!isset($actionData['x']) || !isset($actionData['y'])) {
                    $succes = false;
                    $message = "Coordonnées manquantes pour le déplacement.";
                    break;
                }

                $x = (int)$actionData['x'];
                $y = (int)$actionData['y'];

                if ($x < 1 || $x > 10 || $y < 1 || $y > 10) {
                    $succes = false;
                    $message = "Déplacement hors de la grille.";
                    break;
                }

                if (!$joueurActif->vaisseau->deplacer($x, $y)) {
                    $succes = false;
                    $message = "Déplacement impossible (vaisseau détruit ?).";
                } else {
                    $message = "{$joueurActif->vaisseau->getNom()} se déplace en ({$x},{$y}).";
                }
                break;


            case 'tirer':
                // 🔥 règle distance tir max = 4
                $distanceTir = $this->calculerDistance(
                    $joueurActif->vaisseau->getPosition(),
                    $adversaire->vaisseau->getPosition()
                );

                if ($distanceTir > 4) {
                    $message = "Trop loin ! Tir impossible (distance {$distanceTir} > portée 4)";
                    $succes = false;
                    break;
                }

                $message = $this->resoudreTir($joueurActif, $adversaire);
                $this->verifierFinDePartie();
                break;
                
                
                case 'lancer_drone_attaque':
                    
                    
                    $drone = new Drone('attaque', 30, 5, $joueurActif->vaisseau->getPosition());
$drone->setToursRestants(3); // durée spéciale pour drone attaque

    // Vérifie distance
    $distanceAttaque = $this->calculerDistance(
        $joueurActif->vaisseau->getPosition(),
        $adversaire->vaisseau->getPosition()
    );

    if ($distanceAttaque > 4) {
        $succes = false;
        $message = "Le drone d’attaque ne peut pas frapper : distance {$distanceAttaque} > portée 4";
        break;
    }

    // Création du drone
    $drone = new Drone('attaque', 30, 5, $joueurActif->vaisseau->getPosition());
    $joueurActif->dronesIndependants[] = $drone;

    // Dégâts directs comme tir
    $degatsAttaque = 15; // à toi d’adapter

    $absorbe = 0;
    if ($adversaire->bouclierPoints > 0) {
        $absorbe = min($degatsAttaque, $adversaire->bouclierPoints);
        $adversaire->bouclierPoints -= $absorbe;
        $degatsAttaque -= $absorbe;
    }

    if ($degatsAttaque > 0) {
        $adversaire->vaisseau->subirDegats($degatsAttaque);
    }

    $message = "Drone d’attaque lancé -> dégâts infligés : " . ($degatsAttaque + $absorbe);
    $this->verifierFinDePartie();
    break;



            case 'lancer_drone_bouclier':
                $drone = new Drone('bouclier', 30, 0, $joueurActif->vaisseau->getPosition());
                $joueurActif->dronesIndependants[] = $drone;
                $joueurActif->bouclierPoints += 30;
                $message = "Un drone bouclier renforce le vaisseau.";
                break;


            case 'lancer_drone_sabotage':
                // 🔥 règle distance max sabotage = 3
                $distanceSabotage = $this->calculerDistance(
                    $joueurActif->vaisseau->getPosition(),
                    $adversaire->vaisseau->getPosition()
                );

                if ($distanceSabotage > 3) {
                    $succes = false;
                    $message = "Drone sabotage trop loin (distance {$distanceSabotage} > 3)";
                    break;
                }

                $drone = new Drone('sabotage', 30, 5, $joueurActif->vaisseau->getPosition());
                $joueurActif->dronesIndependants[] = $drone;
                $adversaire->malusTirTours += 2;
                $message = "Drone sabotage lancé avec succès.";
                break;


            case 'lancer_drone_reparation':
                $drone = new Drone('reparation', 30, 0, $joueurActif->vaisseau->getPosition());
                $joueurActif->dronesIndependants[] = $drone;
                $avant = $joueurActif->vaisseau->getEnergie();
                $joueurActif->vaisseau->soigner(20);
                $apres = $joueurActif->vaisseau->getEnergie();
                $message = "Réparation effectuée (+20 énergie).";
                break;


            case 'lancer_drone_kamikaze':
                // 🔥 règle distance EXACTE = 1
                $distanceKamikaze = $this->calculerDistance(
                    $joueurActif->vaisseau->getPosition(),
                    $adversaire->vaisseau->getPosition()
                );

                if ($distanceKamikaze !== 1) {
                    $succes = false;
                    $message = "Explosion impossible : il faut être adjacent (distance {$distanceKamikaze})";
                    break;
                }

                $degats = 30;
                $absorbe = 0;

                if ($adversaire->bouclierPoints > 0) {
                    $absorbe = min($degats, $adversaire->bouclierPoints);
                    $adversaire->bouclierPoints -= $absorbe;
                    $degats -= $absorbe;
                }

                if ($degats > 0) {
                    $adversaire->vaisseau->subirDegats($degats);
                }

                $message = "Drone kamikaze explose et inflige {$degats} dégâts !";
                $this->verifierFinDePartie();
                break;


            default:
                $succes = false;
                $message = "Action inconnue.";
        }

        if ($succes && $this->statut === 'en_cours' && !in_array($type, ['initialiser', 'get_status'])) {
            $this->terminerTour();
        }

        $this->nettoyerDrones($joueurActif);
        $this->nettoyerDrones($adversaire);

        return ['succes' => $succes, 'message' => $message];
    }

    private function resoudreTir(Joueur $attaquant, Joueur $cible): string {
        if ($attaquant->vaisseau->estDetruit()) {
            return "Impossible de tirer : vaisseau détruit.";
        }

        $degats = $attaquant->vaisseau->getPuissanceTir();
        $degatsBruts = $degats;

        if ($attaquant->malusTirTours > 0) {
            $degats = max(5, intdiv($degats, 2));
        }

        $absorbe = 0;
        if ($cible->bouclierPoints > 0) {
            $absorbe = min($degats, $cible->bouclierPoints);
            $cible->bouclierPoints -= $absorbe;
            $degats -= $absorbe;
        }

        if ($degats > 0) {
            $cible->vaisseau->subirDegats($degats);
        }

        return "Tir effectué. Dégâts infligés : {$degats}";
    }

    public function getEtatPourClient(): array {
        return [
            'tour_courant'    => $this->tourCourant,
            'joueur_actif_id' => $this->joueurActifId,
            'statut'          => $this->statut,
            'joueur1' => [
                'vaisseau' => [
                    'nom'      => $this->joueur1->vaisseau->getNom(),
                    'energie'  => $this->joueur1->vaisseau->getEnergie(),
                    'position' => $this->joueur1->vaisseau->getPosition()
                ],
                'drones' => $this->joueur1->getDronesData()
            ],
            'joueur2' => [
                'vaisseau' => [
                    'nom'      => $this->joueur2->vaisseau->getNom(),
                    'energie'  => $this->joueur2->vaisseau->getEnergie(),
                    'position' => $this->joueur2->vaisseau->getPosition()
                ],
                'drones' => $this->joueur2->getDronesData()
            ]
        ];
    }
}
?>
