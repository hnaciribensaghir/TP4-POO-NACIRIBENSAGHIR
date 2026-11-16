<?php
// Partie 5 – Sérialisation
class Utilisateur {
    public string $nom;
    public int $age;
    private string $motDePasse;

    public function __construct(string $nom, int $age, string $motDePasse) {
        $this->nom = $nom;
        $this->age = $age;
        $this->motDePasse = $motDePasse;
    }

    public function __serialize(): array {
        return [
            'nom' => $this->nom,
            'age' => $this->age,
            // $motDePasse est volontairement exclu pour des raisons de sécurité
        ];
    }

    public function __unserialize(array $data): void {
        $this->nom = $data['nom'];
        $this->age = $data['age'];
        $this->motDePasse = "";   // à réinitialiser
    }
}

$u = new Utilisateur("Alice", 25, "secret123");
$serialise = serialize($u);
echo "Sérialisé : $serialise" . PHP_EOL;

$restaure = unserialize($serialise);
var_dump($restaure);

// 5.1 : le mot de passe n'apparaît pas dans la chaîne.
// 5.2 : sans __serialize, le mot de passe privé apparaît en clair -> risque de fuite.
// 5.3 : après unserialize, $motDePasse vaut "". En vrai : stocker un hash (password_hash), jamais le clair.
// 5.4 : unserialize sur données utilisateur = PHP Object Injection. Alternative : json_encode / json_decode.