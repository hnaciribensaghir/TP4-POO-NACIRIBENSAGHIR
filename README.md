# TP 4 – POO en PHP : correction


---

## Partie 0 – La première classe

```php
<?php
class Voiture {
    public $marque;
    public $couleur;

    public function setMarque($m) { $this->marque = $m; }
    public function setCouleur($c) { $this->couleur = $c; }

    public function afficher() {
        echo "Marque : " . $this->marque . " | Couleur : " . $this->couleur . PHP_EOL;
    }
}

$v1 = new Voiture();
$v1->setMarque("Toyota");
$v1->setCouleur("Blanche");
$v1->afficher();

$v2 = new Voiture();
$v2->setMarque("Renault");
$v2->setCouleur("Rouge");
$v2->afficher();
```

**0.3** Non. Chaque objet créé avec `new` possède **sa propre copie** des propriétés. `$this` désigne l'objet qui appelle la méthode : `$v1->setMarque()` ne modifie que `$v1`. La classe est le plan, les valeurs sont stockées dans les objets.

---

## Partie 1 – Visibilité et encapsulation

```php
<?php
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
echo $p->getName() . PHP_EOL;   // Sara Hachimi
echo $p->nom . PHP_EOL;         // Hachimi
```

**1.1** Les 3 lignes provoquent une erreur fatale :

| Ligne | Erreur |
|---|---|
| `echo $p->cin;` | `Cannot access private property Personne::$cin` |
| `echo $p->dateNaissance;` | `Cannot access private property Personne::$dateNaissance` |
| `echo $p->getAge();` | `Call to private method Personne::getAge() from global scope` |

Les membres `private` ne sont accessibles que depuis l'intérieur de la classe.

**1.2** Le CIN est une donnée sensible. En `public`, n'importe quel code pourrait la lire **et la modifier** sans contrôle (valeur invalide, usurpation, fuite d'information). L'encapsulation permet de contrôler l'accès (lecture seule, validation, etc.).

**1.3** Non, elle n'est pas inutile : une méthode privée est un **outil interne**. Elle peut être appelée par d'autres méthodes de la même classe, par exemple `__toString()` (Partie 4) : `return "... (" . $this->getAge() . " ans)";`. On expose le résultat, pas le détail du calcul.

**1.4** Depuis l'extérieur, non : l'erreur devient `Cannot access protected property Personne::$cin`. La différence apparaît seulement dans les classes filles, qui peuvent alors accéder à `cin`.

---

## Partie 2 – Constructeurs et destructeur

### 2.1

**2.1.a**
- `new Voiture("Noire")` : fonctionne. Couleur = « Noire », vitesse = 0 (valeur par défaut).
- `new Voiture(120)` : l'entier 120 est passé au paramètre `string $couleur`. En mode faible (par défaut) PHP le **convertit** en `"120"` sans erreur, donc couleur = « 120 », ce qui est absurde. Avec `declare(strict_types=1);` en début de fichier, on obtient une `TypeError`.

**2.1.b**
```php
class Voiture {
    public function __construct(
        public string $couleur = "Blanche",
        public int $vitesse = 0
    ) {}
}
```
**Avantage :** plus court, pas de déclaration répétée des propriétés ni d'affectations `$this->x = $x;`. Moins de code, donc moins d'erreurs.

### 2.2

**2.2.a** Erreur fatale : `Cannot redeclare Personne::__construct()`. PHP ne supporte **pas la surcharge** de méthodes : un nom de méthode ne peut exister qu'une fois dans une classe.

**2.2.b**
```php
public function __construct(string $nom = "", string $prenom = "") {
    $this->nom = $nom;
    $this->prenom = $prenom;
}

$a = new Personne();                    // création « vide »
$b = new Personne("Hachimi", "Sara");   // avec informations
```

**2.2.c (bonus)** Une méthode statique de fabrique est une méthode `static` qui crée et retourne une instance :
```php
public static function depuisTableau(array $data): self {
    return new self($data['nom'], $data['prenom'], $data['dateNaissance'],
                    $data['villeNaissance'], $data['cin']);
}

$p = Personne::depuisTableau(['nom' => 'Hachimi', 'prenom' => 'Sara', /* ... */]);
```
Cela permet d'avoir plusieurs « façons » de construire un objet avec des noms explicites.

### 2.3

**2.3.a** Avec `unset` :
```
Voiture Blanche créée.
Voiture Blanche détruite.
Fin du script.
```

**2.3.b** Sans `unset`, l'objet est détruit à la **fin du script** :
```
Voiture Blanche créée.
Fin du script.
Voiture Blanche détruite.
```

**2.3.c** À la fin du script, PHP détruit les objets restants. La documentation **ne garantit pas** d'ordre précis : lancez le test pour observer l'ordre sur votre version. Ne comptez jamais sur cet ordre dans un vrai programme.

**2.3.d**
```php
public function __destruct() {
    echo "Détruire " . __CLASS__ . PHP_EOL;   // Détruire Personne
}
```

---

## Partie 3 – `__get`, `__set`, `__isset`, `__unset`

```php
<?php
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
echo $p->nom . PHP_EOL;         // __get      → Hachimi
var_dump(isset($p->nom));       // __isset    → bool(true)
unset($p->nom);                 // __unset    → nom = ""
var_dump(isset($p->nom));       // → bool(false)
$p->telephone = "0600000000";   // → "Propriété telephone inexistante"
```

**3.2**
- Sans `__set` : erreur fatale `Cannot access private property Personne::$nom`.
- Avec `__set` : PHP appelle automatiquement `__set("nom", "X")`, qui décide quoi faire.

**3.3** Oui, un `__set` qui écrit n'importe quoi dans n'importe quelle propriété **contourne** l'encapsulation (c'est comme tout laisser en `public`). Solution : n'autoriser qu'une liste blanche de propriétés et **valider** les valeurs dans `__set` (ou utiliser des setters dédiés), en lançant une exception si la valeur est invalide.

**3.4** Voir la validation dans `__set` ci-dessus (`strlen($value) < 2` → `InvalidArgumentException`). Test :
```php
try {
    $p->nom = "X";
} catch (InvalidArgumentException $e) {
    echo $e->getMessage();
}
```

---

## Partie 4 – `__toString`

```php
public function __toString(): string {
    return $this->prenom . " " . $this->nom . " (" . $this->getAge() . " ans)";
}

echo $p;   // Sara Hachimi (26 ans)  (l'âge dépend de la date du jour)
```

**4.1** `__toString()` est une méthode **de la classe** : à l'intérieur de la classe, on a accès à tous les membres, y compris les privés. La visibilité s'apprécie selon l'endroit d'où l'appel est écrit, pas selon qui déclenche l'exécution.

**4.2** En PHP 8, `__toString()` a un type de retour `string` implicite. Un entier est converti en chaîne en mode faible (pas d'erreur), mais avec `declare(strict_types=1)` on obtient une `TypeError`. Dans les versions anciennes, c'était une erreur fatale.

**4.3** `__toString()` est appelée **automatiquement** dès que l'objet est utilisé comme une chaîne, alors qu'il faut appeler `getName()` explicitement. Situations pratiques :
1. `echo $p;` ou concaténation `"Bonjour " . $p`.
2. Affichage dans une boucle `foreach` (liste d'objets), journaux/logs, messages d'exception, comparaisons de chaînes, `in_array`...

---

## Partie 5 – Sérialisation

**5.1** La chaîne ressemble à :
```
O:11:"Utilisateur":2:{s:3:"nom";s:5:"Alice";s:3:"age";i:25;}
```
Non, le mot de passe n'y figure pas, car `__serialize()` l'exclut.

**5.2** Sans `__serialize()`, **toutes** les propriétés sont sérialisées, y compris les privées (nom marqué par des octets nuls) :
```
... s:23:"\0Utilisateur\0motDePasse";s:9:"secret123"; ...
```
Le mot de passe est visible **en clair** dans la chaîne. Si elle est stockée dans une session, un fichier ou un cookie, elle peut fuiter.

**5.3** `$motDePasse` vaut `""` (`__unserialize` le réinitialise). Dans une vraie application on ne sérialise jamais un mot de passe : on stocke uniquement un **hash** (`password_hash`) en base de données, et on redemande une authentification quand c'est nécessaire.

**5.4** `unserialize()` recrée des objets et peut déclencher leurs méthodes magiques (`__wakeup`, `__destruct`...). Un attaquant qui contrôle la chaîne peut fabriquer des objets de classes existantes dans l'application pour exécuter du code malveillant : c'est la **PHP Object Injection**. Alternative : échanger des données avec `json_encode()` / `json_decode()`, qui ne produisent que des données (tableaux, chaînes, nombres), jamais des objets arbitraires. (Si on doit utiliser `unserialize` sur des données non fiables : `['allowed_classes' => false]`.)

---

## Partie 6 – Héritage

### 6.1

```php
class Chat extends Animal {
    public function miauler(): void { echo "Miaou !" . PHP_EOL; }
}
```

**6.1.b** Non : `Chien` hérite d'`Animal`, pas de `Chat`. `miauler()` n'existe pas dans `Chien` → `Call to undefined method Chien::miauler()`.

### 6.2

**6.2.a** Sans `parent::__construct($nom)`, le constructeur de `Personne` n'est pas exécuté : `$nom` n'est jamais initialisé. En appelant `presentation()` : `Typed property Personne::$nom must not be accessed before initialization`. Contrairement à d'autres langages, PHP n'appelle **pas** automatiquement le constructeur du parent.

**6.2.b** Parce que `Etudiant` (classe fille) a besoin d'y accéder (`$this->nom`). Avec `private`, seule `Personne` y aurait accès. `protected` = accessible dans la classe et ses filles, mais toujours pas de l'extérieur.

### 6.3

**6.3.a**
1. `class Fille extends Base { public function afficher() {} }` → `Cannot override final method Base::afficher()`.
2. `class SousOutil extends Outil {}` → `Class SousOutil cannot extend final class Outil` (selon la version : `may not inherit from final class`).
3. `$p->reference = "REF-002";` → `Cannot modify readonly property Produit::$reference`.

**6.3.b**
- `readonly` : un identifiant (`id`, `reference`, ISBN d'un livre), une date de création, un numéro de compte : des valeurs qui ne doivent jamais changer après la création.
- `final` : une méthode de sécurité ou de calcul critique qu'aucune classe fille ne doit altérer (ex. méthode de vérification de mot de passe, calcul d'une signature), ou une classe utilitaire/valeur qu'on ne veut pas étendre.

### 6.4 `Etudiant extends Personne`

Dans `Personne` (parties 1 à 4), `getAge()` doit être **`protected`** pour être utilisable par `Etudiant` (voir 6.4.a).

```php
<?php
class Personne {
    public function __construct(
        protected string $nom,
        protected string $prenom,
        private string $dateNaissance,
        private string $villeNaissance,
        private string $cin
    ) {}

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
```

**6.4.a** Avec `getAge()` privée dans `Personne` : `Call to private method Personne::getAge() from scope Etudiant`. Il faut la déclarer **`protected`** (ou `public`).

**6.4.b** `dateNaissance` est `private` dans `Personne` : elle appartient à `Personne` uniquement et n'est pas visible dans `Etudiant`, même si un étudiant *est une* personne. Un accès en lecture donne `Warning: Undefined property: Etudiant::$dateNaissance`. Pour la rendre accessible aux filles, il faut la passer en `protected`.

**6.4.c** On évite la duplication de code (principe DRY) : si le format d'affichage de `Personne` change, `Etudiant` est automatiquement à jour. On étend le comportement au lieu de le réécrire.

---

## Partie 7 – Compte bancaire

**7.1** Erreur de syntaxe : `$this->montant 1 * $this->interet / 100` : il manque l'opérateur `+` entre `$this->montant` et `1`.

**7.2** Formule fausse : même avec un `+`, `montant * (montant + 1 * interet / 100)` multiplie le montant par lui-même (le montant au carré). La bonne formule, pour un taux `t` %, est :

```
montant_nouveau = montant × (1 + t / 100)
```
Après `n` ans : `montant × (1 + t/100)^n`.

**7.3** Non. L'énoncé parle de « montant initial et intérêt » : le constructeur devrait être `(montantInitial, interet)`. Dans le code, l'ordre est inversé, donc `new Compte(400, 10)` créerait un compte de 10 DH à 400 % sans aucune erreur (les deux paramètres étant des nombres). C'est un risque d'inversion silencieuse.

**7.4 Code corrigé**

```php
<?php
class Compte {
    private float $montant;
    private float $interet;   // en pourcentage

    public function __construct(float $montantInitial, float $interet) {
        $this->montant = $montantInitial;
        $this->interet = $interet;
    }

    public function un_an(): void {
        $this->montant = $this->montant * (1 + $this->interet / 100);
    }

    public function get_montant(): float {
        return $this->montant;
    }
}

$c1 = new Compte(400, 10);
$c2 = new Compte(2500, 5);

for ($i = 1; $i <= 10; $i++) {
    $c1->un_an();
    $c2->un_an();
}

echo '<p> Valeur du compte = ' . number_format($c1->get_montant(), 2, ',', ' ') . ' dirhams </p>';
echo '<p> Valeur du compte = ' . number_format($c2->get_montant(), 2, ',', ' ') . ' dirhams </p>';
```

**Résultats après 10 ans :**

| Compte | Calcul | Résultat |
|---|---|---|
| `$c1` | 400 × 1,1¹⁰ | **1 037,50 DH** |
| `$c2` | 2 500 × 1,05¹⁰ | **4 072,24 DH** |

---

## Questions de synthèse

1. **Classe / objet :** la classe est le modèle (propriétés + méthodes) ; l'objet est une instance concrète de cette classe, créée avec `new`, avec ses propres valeurs.
2. **Avantages de la POO :** (a) **réutilisation** via l'héritage ; (b) **encapsulation** : données protégées et code mieux organisé ; (c) **maintenance/évolution** plus faciles (modèle proche du monde réel, modification localisée dans une classe). On peut aussi citer le polymorphisme et le travail en équipe.
3. **Pourquoi `private` / `protected` :** pour protéger l'état interne de l'objet, valider les valeurs via des méthodes, et pouvoir changer l'implémentation sans casser le code qui utilise la classe.
4. **`private` vs `protected` :** `private` = uniquement dans la classe qui le déclare (les filles n'y ont pas accès) ; `protected` = dans la classe **et** ses classes filles.
5. **Méthodes magiques :** oui, elles doivent être `public` (sinon PHP émet un avertissement). Exceptions tolérées : `__construct()`, `__destruct()` et `__clone()`, qui peuvent être `private` ou `protected` (par exemple pour un singleton ou pour empêcher la création/le clonage d'objets depuis l'extérieur).