<?php
// Partie 6 – Héritage

// 6.1 : Animal / Chien / Chat
class Animal {
    public function manger(): void { echo "Je mange." . PHP_EOL; }
}
class Chien extends Animal {
    public function aboyer(): void { echo "Wouf !" . PHP_EOL; }
}
class Chat extends Animal {
    public function miauler(): void { echo "Miaou !" . PHP_EOL; }
}

$c = new Chien();
$c->manger();
$c->aboyer();
// $c->miauler();   // 6.1.b : erreur, Chien n'hérite pas de Chat

// 6.3 : final et readonly
class Base {
    final public function afficher(): void { echo "Méthode finale" . PHP_EOL; }
}
final class Outil {
    public const VERSION = "1.0";
}
class Produit {
    public function __construct(public readonly string $reference) {}
}

$prod = new Produit("REF-001");
echo $prod->reference . PHP_EOL;
// $prod->reference = "REF-002";   // Cannot modify readonly property Produit::$reference
// class Fille extends Base { public function afficher() {} }  // Cannot override final method
// class SousOutil extends Outil {}                            // cannot extend final class

// 6.4 : Etudiant extends Personne
class Personne {
    public function __construct(
        protected string $nom,
        protected string $prenom,
        private string $dateNaissance,
        private string $villeNaissance,
        private string $cin
    ) {}

    // protected : accessible dans Etudiant (6.4.a)
    protected function getAge(): int {
        return (new DateTime($this->dateNaissance))->diff(new DateTime())->y;
    }

    public function __toString(): string {
        return $this->prenom . " " . $this->nom . " (" . $this->getAge() . " ans)";
    }
}

class Etudiant extends Personne {
    private string $cne;

    public function __construct(
        string $nom,
        string $prenom,
        string $dateNaissance,
        string $villeNaissance,
        string $cin,
        string $cne
    ) {
        parent::__construct($nom, $prenom, $dateNaissance, $villeNaissance, $cin);
        $this->cne = $cne;
    }

    public function getCne(): string {
        return $this->cne;
    }

    public function __toString(): string {
        return parent::__toString() . " - CNE : " . $this->cne;
    }
}

$etudiants = [];
$etudiants[] = new Etudiant("Hachimi", "Sara", "2004-05-14", "Marrakech", "EE111111", "R130000001");
$etudiants[] = new Etudiant("Alaoui", "Youssef", "2003-11-02", "Safi", "EE222222", "R130000002");
$etudiants[] = new Etudiant("Benani", "Imane", "2004-01-20", "Casablanca", "EE333333", "R130000003");
$etudiants[] = new Etudiant("Tazi", "Omar", "2003-07-09", "Rabat", "EE444444", "R130000004");
$etudiants[] = new Etudiant("Idrissi", "Khadija", "2004-09-30", "Fès", "EE555555", "R130000005");

foreach ($etudiants as $e) {
    echo $e . PHP_EOL;
}

// 6.2.a : sans parent::__construct(), $nom n'est pas initialisé -> erreur "must not be accessed before initialization".
// 6.2.b : protected permet aux classes filles d'accéder à $nom.
// 6.4.a : getAge() privée -> erreur ; il faut protected (ou public).
// 6.4.b : dateNaissance est privée dans Personne, invisible dans Etudiant.
// 6.4.c : parent::__toString() évite la duplication de code.