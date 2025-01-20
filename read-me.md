# Documentation Technique du CRM
Version 1.0.0

## Table des matières
1. [Introduction](#introduction)
2. [Architecture du système](#architecture-du-système)
3. [Structure du projet](#structure-du-projet)
4. [Base de données](#base-de-données)
5. [Authentification et sécurité](#authentification-et-sécurité)
6. [Gestion des utilisateurs](#gestion-des-utilisateurs)
7. [Système de cache](#système-de-cache)
8. [API et intégrations](#api-et-intégrations)
9. [Interface utilisateur](#interface-utilisateur)
10. [Performances et optimisation](#performances-et-optimisation)
11. [Déploiement](#déploiement)
12. [Maintenance](#maintenance)
13. [Guide de contribution](#guide-de-contribution)
14. [Dépannage](#dépannage)

## Introduction

### Vue d'ensemble
Ce CRM est construit sur un framework PHP personnalisé utilisant une architecture MVC moderne. Il est conçu pour gérer les relations clients, le suivi des ventes, et l'administration des utilisateurs.

### Technologies principales
- PHP 8.0+
- MySQL/MariaDB
- Bootstrap 4.5.3
- jQuery 3.6.0
- DataTables 1.13.6

### Prérequis
- PHP >= 8.0
- Extensions PHP: PDO, JSON, mbstring, OpenSSL
- MySQL/MariaDB
- Composer
- Serveur web compatible (Apache/Nginx)

## Architecture du système

### Pattern MVC
Le CRM suit strictement le pattern MVC avec quelques particularités :

```
Request → Middleware → Controller → Service → Model → Database
   ↑          ↓           ↓           ↓         ↓         ↓
   └──────────────────── Response ────────────────────────┘
```

### Composants clés

#### Controllers
Les contrôleurs principaux incluent :
- `AdminController`: Gestion administrative
- `AuthController`: Authentification
- `ApiController`: Points d'entrée API

#### Services
Services majeurs :
- `UserService`: Gestion des utilisateurs
- `BalanceService`: Calculs financiers
- `ValidationService`: Validation des données

#### Models
Modèles principaux :
- `User`: Gestion des utilisateurs
- `Sale`: Gestion des ventes
- `Invoice`: Facturation
- `Lead`: Prospects

### Middleware System
```php
// Configuration type des middlewares
$App->use("/.*", \Middlewares\SessionMiddleware::class);
$App->use("/admin/.*", \Middlewares\AuthMiddleware::class);
$App->use("/.*", \Middlewares\CacheMiddleware::class);
```

## Structure du projet

```
src/
├── app/
│   ├── Controllers/
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   └── ApiController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Sale.php
│   │   └── Invoice.php
│   ├── Services/
│   │   ├── UserService.php
│   │   └── BalanceService.php
│   ├── Middlewares/
│   └── Observers/
├── template/
│   ├── admin/
│   │   ├── login.blade.php
│   │   ├── userlist.blade.php
│   │   └── useradd.blade.php
│   └── layouts/
├── public/
└── vendor/
```

## Base de données

### Modèle de données
Tables principales :
- `user`: Stockage des utilisateurs
- `sale`: Transactions de vente
- `invoice`: Factures
- `lead`: Prospects/leads

### Schéma de la table Users
```php
public static $SCHEMA = array(
    "userId" => array(
        "field" => "userid",
        "fieldType" => "int",
        "type" => "int",
        "default" => null
    ),
    // ... autres champs
);
```

## Authentification et sécurité

### Système d'authentification
Géré par `AuthController` et `AuthMiddleware`.

```php
class AuthController extends Controller
{
    public function login()
    {
        // Vérification de la session existante
        if ($this->session->get('connexion')) {
            $userType = $this->getUserType();
            \Classes\redirectByUserType($userType);
        }
        
        // Traitement de la connexion
        // ...
    }
}
```

### Middleware de sécurité
```php
class AuthMiddleware extends Middleware
{
    private const PUBLIC_PATHS = [
        '/loginuser/',
        '/login.php',
        '/logout/'
    ];

    public function handle(HttpRequest $request, HttpResponse $response): HttpResponse
    {
        // Vérification de l'authentification
        // ...
    }
}
```

## Gestion des utilisateurs

### Types d'utilisateurs
- Admin: Accès complet
- Client: Accès restreint aux fonctionnalités client
- Provider: Accès aux fonctionnalités fournisseur

### Service utilisateur
```php
class UserService
{
    public function getUserById(int $userId): User|bool|null
    {
        try {
            return $this->user->get($userId);
        } catch (\Exception $e) {
            throw new \RuntimeException("Erreur lors de la récupération du compte", 0, $e);
        }
    }
}
```

### Validation des données utilisateur
```php
class UserValidationService
{
    public function validateUser(array $data): ConstraintViolationListInterface
    {
        $constraints = new Assert\Collection([
            'fields' => $this->getFieldsConstraints(),
            'allowExtraFields' => true,
            'allowMissingFields' => true
        ]);
        // ...
    }
}
```

## Système de cache

### Architecture du cache
Le système utilise un pattern Observer pour la gestion du cache :

```php
class CacheObserver 
{
    public function created(Model $model): void 
    {
        $this->handleModelChange($model, 'created');
    }

    public function updated(Model $model): void 
    {
        $this->handleModelChange($model, 'updated');
    }
}
```

### Stratégies de cache
- Cache par utilisateur
- Cache des soldes
- Cache des listes globales

### Invalidation intelligente
```php
private function handleUserChange(User $user, string $action): void
{
    $cache = $this->cacheManager->getCacheAdapter();
    $cache->delete('UserList');
    if ($action !== 'created') {
        $cache->delete('balance_user_' . $user->{$user::$OBJ_INDEX});
    }
}
```

## API et intégrations

### Points d'entrée API
```php
$App->all("/apiv2/{resource}")
    ->setAction("Api\\ApiV2Controller@handleRequest")
    ->where('resource', '[a-zA-Z]+');
```

### Webhooks
Gérés par `WebhookController` pour les intégrations externes.

```php
class Webhook extends Controller
{
    public function receive($params)
    {
        // Validation et traitement des webhooks
        // ...
    }
}
```

## Interface utilisateur

### Templates Blade
Le système utilise le moteur de template Blade modifié :

```php
@extends('admin.blanck')

@section('content')
    // Contenu de la page
@endsection
```

### Composants réutilisables
- Formulaires
- Messages d'erreur/succès
- Menus de navigation

### DataTables
Integration et configuration :
```javascript
var table = $('.dataTable').DataTable({
    deferRender: true,
    scrollY: 4000,
    scrollCollapse: true,
    scroller: true,
    // ...
});
```

## Performances et optimisation

### Cache système
- Utilisation de Redis/File système
- Cache des requêtes fréquentes
- Invalidation intelligente

### Optimisation des requêtes
- Utilisation d'index
- Pagination des résultats
- Chargement différé

### Monitoring
- DebugBar intégré
- Logs détaillés des requêtes SQL
- Surveillance des performances

## Déploiement

### Environnements
- Développement
- Staging
- Production

### Configuration
```env
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=your_database
DB_USER=your_user
DB_PASSWORD=your_password
```

### Processus de déploiement
1. Préparation du code
2. Sauvegarde de la base de données
3. Mise à jour des dépendances
4. Migration de la base de données
5. Tests de non-régression

## Maintenance

### Tâches régulières
- Nettoyage des caches
- Optimisation de la base de données
- Vérification des logs

### Gestion des erreurs
```php
set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno)
{
    if ($severity == E_ERROR || $severity == E_USER_ERROR) {
        throw new ErrorException($message, 0, $severity, $filename, $lineno);
    }
}
```

## Guide de contribution

### Standards de code
- PSR-1, PSR-4, PSR-12
- Documentation PHPDoc obligatoire
- Tests unitaires pour les nouvelles fonctionnalités

### Processus de développement
1. Création d'une branche feature
2. Développement et tests
3. Code review
4. Merge dans develop
5. Tests d'intégration
6. Merge dans master

## Dépannage

### Problèmes courants
1. Problèmes de cache
   - Solution: Vider le cache système
   - Commande: `?clearcache=1`

2. Problèmes de session
   - Solution: Réinitialiser la session
   - Commande: `?clearsession=1`

3. Erreurs de base de données
   - Vérifier les logs
   - Vérifier les connexions
   - Optimiser les requêtes

### Logging
```php
Logger::debug("Message de debug", [
    'context' => $context,
    'data' => $data
]);
```

## Évolution du système

### Améliorations prévues
1. Mise à jour vers PHP 8.1+
2. Amélioration du système de cache
3. Optimisation des performances
4. Nouvelle interface d'administration

### Roadmap
- Version 1.1: Amélioration de la sécurité
- Version 1.2: Nouvelle API REST
- Version 1.3: Interface responsive
- Version 2.0: Refonte complète de l'interface

## Support et ressources

### Documentation supplémentaire
- API Documentation: `/apiv2/docs`
- Guide d'utilisation: [lien]
- Wiki technique: [lien]

### Contact
- Support technique: [email]
- Bug reports: [lien]
- Feature requests: [lien]