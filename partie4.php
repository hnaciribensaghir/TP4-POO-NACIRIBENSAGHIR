<?php
// Partie 4 – __toString
class Personne {
    public string $nom;
    public string $prenom;
    private string $dateNaissance;

    public function __construct(string $nom, string $prenom, string $dateNaissance) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->dateNaissance = $dateNaissance;
    }

    private function getAge(): int {
        return (new DateTime($this->dateNaissance))->diff(new DateTime())->y;
    }

    public function __toString(): string {
        return $this->prenom . " " . $this->nom . " (" . $this->getAge() . " ans)";
    }
}

$p = new Personne("Hachimi", "Sara", "2000-05-14");
echo $p . PHP_EOL;

// 4.1 : getAge() est appelée depuis l'intérieur de la classe -> accès autorisé aux membres privés.
// 4.2 : un entier est converti en chaîne en mode faible ; TypeError avec strict_types=1.
// 4.3 : __toString est appelée automatiquement : echo $p, concaténation, logs, messages d'exception...