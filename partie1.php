<?php
// Partie 1 – Visibilité et encapsulation
class Personne {
    public string $nom;
    public string $prenom;
    private string $dateNaissance;
    private string $villeNaissance;
    private string $cin;

    public function __construct(
        string $nom,
        string $prenom,
        string $dateNaissance,
        string $villeNaissance,
        string $cin
    ) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->dateNaissance = $dateNaissance;
        $this->villeNaissance = $villeNaissance;
        $this->cin = $cin;
    }

    public function getName(): string {
        return $this->prenom . " " . $this->nom;
    }

    private function getAge(): int {
        $naissance = new DateTime($this->dateNaissance);
        $aujourdhui = new DateTime();
        return $naissance->diff($aujourdhui)->y;
    }
}

$p = new Personne("Hachimi", "Sara", "2000-05-14", "Marrakech", "EE123456");
echo $p->getName() . PHP_EOL;
echo $p->nom . PHP_EOL;

// Expérimentation (décommentez une ligne à la fois) :
// echo $p->cin;             // Cannot access private property Personne::$cin
// echo $p->dateNaissance;   // Cannot access private property Personne::$dateNaissance
// echo $p->getAge();        // Call to private method Personne::getAge() from global scope

// 1.2 : cin public = lisible et modifiable par n'importe qui (donnée sensible).
// 1.3 : getAge() privée est un outil interne, utilisable par d'autres méthodes (ex. __toString).
// 1.4 : cin protected -> même erreur depuis l'extérieur ; accessible seulement dans les classes filles.