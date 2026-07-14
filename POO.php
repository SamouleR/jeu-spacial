<?php 
class Personne {
private $nom;
private $age;
private $sexe;

    public function __construct($nom,$age,$sexe){
        $this->nom = $nom;
        $this->age = $age;
        $this->sexe = $sexe;
    }
}
class Mecanos extends Personne {
    private $kitRep;
    
    public function __construct($nom,$age,$sexe,$kitRep){
        parent::__construct($nom,$age,$sexe);
        $this->kitRep = $kitRep;
    }
    
    public function reparer (){
        
    }
}

class vaisseau {
    
}

class drone {
    
}


?>