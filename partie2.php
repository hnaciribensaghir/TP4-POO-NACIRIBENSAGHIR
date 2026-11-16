<?php
// Partie 2 – Constructeurs et destructeur

// 2.1 : constructeur avec valeurs par défaut + promotion de propriétés (PHP 8)
class Voiture {
    public function __construct(
        public string $couleur = "Blanche",
        public int $vitesse = 0
    ) {}

    public function afficher(): void {
        echo "Couleur : {$this->couleur} | Vitesse : {$this->vitesse} km/h" . PHP_EOL;
    }

    public function __destruct() {
        echo "Voiture {$this->couleur} détruite." . PHP_EOL;
    }
}

$v1 = new Voiture();             // valeurs par défaut
$v2 = new Voiture("Rouge", 50);  // valeurs personnalisées
$v1->afficher();
$v2->afficher();

// 2.1.a : new Voiture("Noire") -> couleur Noire, vitesse 0.
//         new Voiture(120)     -> converti en "120" (mode faible) ; TypeError avec strict_types=1.
// 2.1.b : avantage de la promotion : moins de code (pas de déclaration + affectation séparées).

// 2.2 : deux constructeurs -> Fatal error: Cannot redeclare __construct().
// Solution : un seul constructeur avec valeurs par défaut.
class Personne {
    public string $nom;
    public string $prenom;

    public function __construct(string $nom = "", string $prenom = "") {
        $this->nom = $nom;
        $this->prenom = $prenom;
    }

    // Bonus 2.2.c : méthode statique de fabrique
    public static function depuisTableau(array $data): self {
        return new self($data['nom'] ?? "", $data['prenom'] ?? "");
    }

    // 2.3.d : destructeur
    public function __destruct() {
        echo "Détruire " . __CLASS__ . PHP_EOL;
    }
}

$a = new Personne();
$b = new Personne("Hachimi", "Sara");
$c = Personne::depuisTableau(['nom' => 'Alaoui', 'prenom' => 'Youssef']);

// 2.3 : avec unset($obj) l'objet est détruit tout de suite ;
//       sans unset, il est détruit à la fin du script.
//       L'ordre de destruction à la fin du script n'est pas garanti : testez chez vous.
echo "Fin du script." . PHP_EOL;