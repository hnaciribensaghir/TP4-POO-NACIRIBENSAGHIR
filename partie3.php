<?php
// Partie 3 – __get, __set, __isset, __unset
class Personne {
    private string $nom = "";
    private string $prenom = "";
    private string $dateNaissance = "";

    public function __set(string $name, mixed $value): void {
        if (!property_exists($this, $name)) {
            echo "Propriété $name inexistante" . PHP_EOL;
            return;
        }
        // 3.4 : validation du nom
        if ($name === 'nom') {
            if (!is_string($value) || strlen($value) < 2) {
                throw new InvalidArgumentException("Le nom doit contenir au moins 2 caractères.");
            }
        }
        $this->$name = $value;
    }

    public function __get(string $name): mixed {
        return property_exists($this, $name) ? $this->$name : null;
    }

    public function __isset(string $name): bool {
        return property_exists($this, $name) && $this->$name !== "";
    }

    public function __unset(string $name): void {
        if (property_exists($this, $name)) {
            $this->$name = "";
        }
    }
}

$p = new Personne();
$p->nom = "Hachimi";            // __set
echo $p->nom . PHP_EOL;         // __get
var_dump(isset($p->nom));       // __isset -> true
unset($p->nom);                 // __unset
var_dump(isset($p->nom));       // false
$p->telephone = "0600000000";   // "Propriété telephone inexistante"

try {
    $p->nom = "X";              // trop court
} catch (InvalidArgumentException $e) {
    echo "Erreur : " . $e->getMessage() . PHP_EOL;
}

// 3.2 : sans __set -> erreur "Cannot access private property". Avec __set -> PHP l'appelle automatiquement.
// 3.3 : __set peut casser l'encapsulation -> il faut une liste blanche + validation + exceptions.