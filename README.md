# CRM Compleo 

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![Framework](https://img.shields.io/badge/Framework-Compleo-orange.svg)](https://github.com/COMPLEOAGENCY/Framework)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)]()

> Une application CRM moderne et puissante construite avec le Framework Compleo

## Présentation

> **Note :** Cette documentation concerne la partie moderne de l'application 2CRM, située dans le répertoire `/src`.

Le CRM Compleo est une solution complète de gestion de la relation client, développée avec le Framework Compleo. Cette application PHP moderne suit une architecture MVC (Modèle-Vue-Contrôleur) et intègre les meilleures pratiques de développement.

Pour plus de détails sur le framework utilisé, consultez la [documentation du Framework Compleo](https://github.com/COMPLEOAGENCY/Framework).

### Caractéristiques Principales

- ⚡️ Application PHP 8.1+
- 🏗️ Architecture MVC
- 👥 Gestion avancée des utilisateurs et des rôles
- 🚄 Système de cache intégré
- 🌍 Support multi-langues
- 📚 API REST documentée
- 💅 Interface utilisateur moderne
- ✅ Système de validation robuste
- 🔒 Gestion des sessions sécurisée

### Technologies Clés

- **PHP 8.1+** - Langage de base
- **Framework Compleo** - Framework principal
- **Illuminate** - Composants Laravel
- **Redis** - Système de cache
- **Swagger** - Documentation API
- **Symfony** - Composants divers
- **Bugsnag** - Gestion des erreurs
- **Clockwork** - Debugging

## Table des Matières

### Architecture
- [Structure du Projet](#structure-du-projet)
- [Framework Compleo](#framework-compleo)
- [Patterns de Conception](#patterns-de-conception)

### Base de Données 
- [Structure de la Base de Données](#structure-de-la-base-de-données)

### Models
- [Héritage de Model](#heritage-de-model)
- [Liste des Models](#liste-des-models)
- [Relations](#relations)
- [Liste des Methodes héritées de Model](#liste-des-methodes)

### Composants Principaux
- [Système d'Authentication](#système-dauthentication)
- [Gestion des Utilisateurs](#gestion-des-utilisateurs)
- [Gestion des Sessions](#gestion-des-sessions)
- [Système de Cache](#système-de-cache)
- [Validation des Données](#validation-des-données)
- [Gestion des Messages](#gestion-des-messages)

### Services
- [UserService](#userservice)
- [ValidationService](#validationservice)
- [BalanceService](#balanceservice)
- [MessageService](#messageservice)

### API
- [Documentation Swagger](#documentation-swagger)
- [Points d'Entrée](#points-dentrée)
- [Authentication](#authentication)
- [Formats de Réponse](#formats-de-réponse)

### Interface Utilisateur
- [Structure des Templates](#structure-des-templates)
- [Assets](#assets)
- [JavaScript](#javascript)
- [Composants UI](#composants-ui)

### Performance
- [Cache System](#cache-system)
- [Database Optimization](#database-optimization)
- [Redis Integration](#redis-integration)

### Debugging & Monitoring
- [Clockwork Integration](#clockwork-integration)
- [Bugsnag Error Tracking](#bugsnag-error-tracking)
- [Logging System](#logging-system)

## Structure du Projet

```
src/
├── app/                        # Cœur de l'application
│   ├── Classes/               # Classes utilitaires (CrmFunctions, Helpers, Logger)
│   ├── Controllers/           # Contrôleurs (Admin, Auth, Api)
│   │   └── Api/              # Contrôleurs API
│   ├── Middlewares/          # Middlewares (Auth, Cache, Session)
│   ├── Models/               # Modèles de données
│   │   └── Adapters/        # Adaptateurs de modèles
│   ├── Observers/           # Observateurs (CacheObserver)
│   ├── Services/            # Services métier (Balance, User)
│   │   └── Validation/     # Services de validation
│   ├── Traits/             # Traits PHP (ModelObservable)
│   └── sql/                # Fichiers SQL
│
├── config/                    # Configuration
│   ├── config.cached.php
│   ├── index.php
│   └── settings.php
│
├── docs/                      # Documentation
├── public/                    # Fichiers publics
│
├── template/                  # Templates et vues
│   ├── admin/               # Interface administrateur
│   │   └── form/           # Formulaires admin
│   └── api/                # Templates API
```

## Framework Compleo

Le Framework Compleo est un framework PHP moderne et léger qui sert de base à cette application CRM. Il fournit une structure robuste et des fonctionnalités essentielles :

### Caractéristiques du Framework

- **Architecture MVC** - Organisation claire du code en Modèles, Vues et Contrôleurs
- **Système de Routing** - Gestion flexible des routes et des endpoints
- **Middleware System** - Pipeline de middlewares pour le traitement des requêtes
- **ORM Intégré** - Manipulation simplifiée des données avec l'ORM
- **Gestion de Cache** - Système de cache performant avec support Redis
- **Sécurité** - Mécanismes de sécurité intégrés (XSS, CSRF, SQL Injection)
- **Validation** - Système complet de validation des données
- **Template Engine** - Moteur de template puissant et flexible

Pour une documentation complète du framework, ses fonctionnalités et son utilisation, consultez le [README officiel du Framework Compleo](https://github.com/COMPLEOAGENCY/Framework).

## Patterns de Conception

L'application utilise plusieurs patterns de conception pour maintenir un code propre, modulaire et maintenable :

### 1. Pattern Observer
- Implémenté via `ModelObservable` trait et `CacheObserver`
- Permet l'invalidation intelligente du cache lors des modifications de données
- Utilisé pour la synchronisation entre les modèles et le cache
- Exemple : Mise à jour automatique des soldes utilisateurs lors des modifications de ventes

### 2. Pattern Middleware
- Chaîne de responsabilité pour le traitement des requêtes HTTP
- Middlewares clés :
  - `AuthMiddleware` : Gestion de l'authentification et des autorisations
  - `CacheMiddleware` : Optimisation des performances via le cache
  - `SessionMiddleware` : Gestion des sessions utilisateur

### 3. Pattern Service
- Encapsulation de la logique métier complexe
- Services principaux :
  - `BalanceService` : Calcul et gestion des soldes utilisateurs
  - `UserService` : Gestion des opérations utilisateur
  - `ValidationService` : Validation des données

### 4. Pattern Model-View-Controller (MVC)
- Structure claire avec séparation des responsabilités :
  - Modèles : Gestion des données et logique métier (`app/Models/`)
  - Vues : Templates pour l'affichage (`template/`)
  - Contrôleurs : Gestion des requêtes (`app/Controllers/`)

### 5. Pattern Repository
- Abstraction de la couche de données via la classe `Model`
- Gestion unifiée du cache et des requêtes
- Utilisation de schémas pour la validation des données

### 6. Pattern Adapter
- Présent dans `Models/Adapters/`
- Permet l'intégration flexible avec différentes sources de données
- Standardise les interfaces de données

Pour plus de détails sur l'implémentation de ces patterns, consultez la [documentation du Framework Compleo](https://github.com/COMPLEOAGENCY/Framework).

## Système de Cache

### CacheManager
- Gère l'interface principale avec Redis
- Accessible via un singleton : `CacheManager::instance()`
- Fournit un adaptateur de cache via `getCacheAdapter()`

Exemple d'utilisation :
```php
// Exemple d'utilisation du cache pour le solde utilisateur
$cache = CacheManager::instance()->getCacheAdapter();
$balanceCache = $cache->getItem("balance_user_$userId");

// Vérification si présent en cache
if ($balanceCache->isHit()) {
    return $balanceCache->get();
}

// Si pas en cache, calcul et mise en cache
$result = /* calcul du solde */;
$balanceCache->set($result);
$cache->save($balanceCache);
```

### Middleware de Cache (`app/Middlewares/CacheMiddleware.php`)
- Permet de vider le cache via le paramètre URL `clearcache=1`
- Intégré avec DebugBar pour le monitoring
- Exemple d'utilisation : `votreurl.com?clearcache=1`

### CacheObserver (`app/Observers/CacheObserver.php`)
- Implémente le pattern Observer pour l'invalidation intelligente du cache
- Observe les changements (create/update/delete) sur tous les modèles
- Gère automatiquement l'invalidation des caches selon le type de modèle
- Recalcule les données dépendantes (ex: soldes utilisateurs)

#### Documentation Technique Détaillée

1. **Initialisation du système**
```php
// Dans CacheObserverMiddleware
Model::observe(new CacheObserver());
```
- Le middleware initialise le CacheObserver au démarrage de l'application
- Utilise le trait `ModelObservable` qui permet aux modèles d'être observés

2. **Structure des classes impliquées**
- `Model` (classe abstraite de base)
  - Utilise le trait `ModelObservable`
  - Gère les opérations CRUD de base
  - Notifie les observateurs des changements

- `CacheObserver`
  - Observe tous les modèles
  - Gère l'invalidation intelligente du cache
  - Contient des handlers spécifiques par type de modèle

3. **Flux d'exécution lors d'un save()**
```php
// 1. Appel de save() sur un modèle
$model->save();

// 2. Dans Model::performSave()
$isNew = empty($this->{static::$OBJ_INDEX});
$result = Database::instance()->updateOrInsert(/*...*/);
if ($result !== false) {
    // 3. Notification des observateurs
    $this->notifyObservers($isNew ? 'created' : 'updated');
}

// 4. Dans CacheObserver
public function updated(Model $model): void {
    $this->handleModelChange($model, 'updated');
}

// 5. Gestion spécifique selon le type de modèle
private function handleModelChange(Model $model, string $action): void {
    $cache = $this->cacheManager->getCacheAdapter();
    switch (get_class($model)) {
        case Sale::class:
            $this->handleSaleChange($model);
            break;
        case User::class:
            $this->handleUserChange($model, $action);
            break;
        // ...
    }
}
```

4. **Handlers spécifiques par type**

Pour implémenter un nouveau handler dans le CacheObserver, vous devez :
1. Créer une méthode `handle{ModelName}Change` dans la classe CacheObserver
2. Gérer les clés de cache spécifiques au modèle
3. Implémenter la logique d'invalidation et de recalcul

Les actions par défaut disponibles dans Model sont :
- `created` : Appelé après la création d'un nouvel enregistrement
- `updated` : Appelé après la mise à jour d'un enregistrement existant
- `deleted` : Appelé après la suppression d'un enregistrement

Exemple d'implémentation de handlers :
```php
// Exemple pour User
private function handleUserChange(User $user, string $action): void {
    $cache = $this->cacheManager->getCacheAdapter();
    // Invalide la liste des utilisateurs
    $cache->delete('UserList');
    
    // Invalide le cache du solde sauf pour une création
    if ($action !== 'created') {
        $cache->delete('balance_user_' . $user->{$user::$OBJ_INDEX});
    }
}

// Exemple pour Sale/Invoice
private function handleSaleChange(Sale $sale): void {
    $cache = $this->cacheManager->getCacheAdapter();
    // Une vente modifie le solde de l'utilisateur
    $cache->delete('balance_user_' . $sale->userid);
    
    // Force le recalcul immédiat du solde
    $this->balanceService->getSoldeDetails($sale->userid, true);
}

// Exemple de handler générique pour un nouveau modèle
private function handleCustomModelChange(CustomModel $model, string $action): void {
    $cache = $this->cacheManager->getCacheAdapter();
    
    // 1. Invalider les listes
    $cache->delete('CustomModelList');
    
    // 2. Invalider les caches spécifiques
    switch ($action) {
        case 'created':
            // Gérer la création
            break;
        case 'updated':
            // Gérer la mise à jour
            $cache->delete('custom_model_' . $model->{$model::$OBJ_INDEX});
            break;
        case 'deleted':
            // Gérer la suppression
            $cache->delete('custom_model_' . $model->{$model::$OBJ_INDEX});
            // Nettoyer les caches liés
            break;
    }
    
    // 3. Gérer les dépendances
    if (property_exists($model, 'user_id')) {
        $cache->delete('user_custom_models_' . $model->user_id);
    }
}
```

5. **Points clés du système**
- Invalidation intelligente : seules les clés pertinentes sont invalidées
- Recalcul automatique : certaines données sont recalculées immédiatement
- Gestion des dépendances : les relations entre modèles sont prises en compte
- Performance : utilisation de Redis comme backend de cache
- Monitoring : intégration avec DebugBar pour le suivi

Ce système permet une gestion efficace et automatique du cache, avec une invalidation ciblée selon le type de modèle et l'action effectuée, tout en maintenant la cohérence des données entre les différents modèles liés.

## Models

### Héritage de Model

La classe abstraite `Model` est le cœur du système de modèles de l'application. Elle fournit une base robuste pour l'interaction avec la base de données et implémente des fonctionnalités avancées :

#### 1. Schéma et Typage
```php
public static $SCHEMA = [
    'property_name' => [
        'field' => 'db_field_name',  // Nom du champ en base de données
        'type' => 'type_php',        // Type PHP (int, string, bool, array...)
        'default' => 'value'         // Valeur par défaut
    ]
];
```

#### 2. Propriétés de Configuration
```php
public static $TABLE_NAME;   // Nom de la table en base de données
public static $TABLE_INDEX;  // Clé primaire de la table
public static $OBJ_INDEX;    // Index de l'objet (peut différer de TABLE_INDEX)
```

#### 3. Système de Cache Intelligent
- Intégration avec `CacheObserver` pour l'invalidation automatique
- Utilisation du trait `ModelObservable` pour la notification des changements
- Cache automatique des requêtes fréquentes

### Liste des Models

L'application organise ses modèles en plusieurs catégories fonctionnelles :

#### 1. Gestion des Utilisateurs et Administration
```php
// Utilisateurs et Authentification
Models\User           // Comptes de facturation Tiers (Fourniseeur/Client)
Models\ShopUser       // Comptes utilisateur boutique
Models\CrmUser        // Comptes utilisateur du CRM
Models\Administration // Paramètres système et configuration
```

#### 2. Gestion Commerciale et Finances
```php
// Ventes et Facturation
Models\Sale           // Gestion des ventes
Models\Invoice        // Facturation
Models\InvoicePayment // Suivi des paiements
Models\Purchase       // Gestion des achats

// Campagnes Marketing

Models\UserCampaign   // Association utilisateurs-campagnes
```

#### 3. Gestion des Leads et Contacts
```php
// Leads et Prospects
Models\Campaign      // Liste des Metiers/ catégories de Lead
Models\Lead          // Gestion des prospects
Models\LeadManager   // Administration des leads
Models\Contact       // Informations de contact
Models\Project       // Gestion des projets
Models\Meta          // Métadonnées des leads/projets/Contacts

// Validation et Distribution
Models\Validation           // Règles de validation
Models\ValidationHistory    // Historique des validations
Models\Bloctel              // Validation  Bloctel
```

### Liste des Méthodes

Chaque modèle hérite des méthodes suivantes de la classe `Model` :

#### 1. Méthodes de Base de Données
```php
// Lecture
public function get(int $id)
public function getList($limit = null, array $sqlParameters = null)

// Écriture & Suppression
public function save(): mixed (save & update)
public function delete(int $id): bool
```

#### 2. Méthodes d'Hydratation
```php
public function hydrate(array $data = [], bool $strict = false)
public function importObj(object $obj)
public static function getSchema(): array
```

Pour plus de détails sur l'implémentation des modèles et leurs relations, consultez la [documentation du Framework Compleo](https://github.com/COMPLEOAGENCY/Framework).

## Mises à jour récentes (août 2025)

Ces changements sont effectifs dans le code courant et documentés pour usage immédiat.

- __API v2 Validation Leads__
  - Endpoint `GET/POST /api/v2/validation/run` → `Controllers\\Api\\LeadValidationController::run()` → `Services\\LeadValidationService::run()`
  - Endpoint `GET/POST /api/v2/validation/pending` → `Controllers\\Api\\LeadValidationController::loadPendingLeads()` → `Services\\LeadValidationService::loadPendingLeads()`
  - Définition des routes dans `src/public/index.php`

- __Service UserCampaignService__ (nouveau) dans `src/app/Services/UserCampaignService.php`
  - Méthodes: `getUserCommands($userId)`, `getCommandWebservices($usercampaignId)`, `parseProducts($campaignName)`, `extractReference($campaignName)`, `getAllCommands($limit, $offset, $filters)`, `getCommand($usercampaignId)`
  - Utilisé par `Controllers\\AdminController` pour afficher les commandes et leur webservices dans l’édition utilisateur et pour la liste des campagnes clients

- __AdminController__
  - Injection d’un `UserCampaignService` (`$this->userCampaignService = new \\Services\\UserCampaignService()`)
  - Nouvelles actions/écrans:
    - `clientcampaignList` (route `/admin/clientcampaign/list`) avec filtrage et actions (copie, suppression logique)
    - `verificationList` (route `/admin/verification/list`) et `verificationAdd` (route `/admin/verification/{id}`)

## API v2 - Validation Leads

Endpoints exposés dans `src/public/index.php` et implémentés par `src/app/Controllers/Api/LeadValidationController.php`.

- __/api/v2/validation/run__ (GET/POST)
  - Contrôleur: `LeadValidationController::run()`
  - Service: `LeadValidationService::run(array $params)`
  - Paramètres:
    - `validationid` (int, optionnel): exécuter une configuration précise
    - `statut` (string, optionnel, défaut `on`): filtre de configurations
    - `limit` (int, optionnel, défaut `100`)
  - Réponse: JSON avec `success`, `message`, `input`, `metrics` (durée, nombre de configurations), et `executions` (placeholder)

- __/api/v2/validation/pending__ (GET/POST)
  - Contrôleur: `LeadValidationController::loadPendingLeads()`
  - Service: `LeadValidationService::loadPendingLeads(array $params)`
  - Paramètres:
    - `id` (int, optionnel): identifiant de lead précis (prioritaire)
    - `limit` (int, optionnel, défaut `100`)
    - `days` (int, optionnel, défaut `30`): fenêtre glissante en jours
  - Comportement: utilise `Models\\Lead::getList()` (via instance) avec filtres sur `timestamp` et `statut = 'pending'`
  - Réponse: JSON avec `success`, `message`, `input`, `metrics` (compte, durée) et `leads`

## Services ajoutés/modifiés

- __Services\\LeadValidationService__ (`src/app/Services/LeadValidationService.php`)
  - `run(array $params = [])`: charge les configurations de validation (`Models\\Validation::getList`) selon `validationid`/`statut`/`limit` et retourne des métriques d’exécution (placeholder)
  - `loadPendingLeads(array $params = [])`: charge les leads en `pending` par fenêtre temporelle (`days`) ou par `id` donné

- __Services\\UserCampaignService__ (`src/app/Services/UserCampaignService.php`)
  - Agrège les données des campagnes clients (commandes) et leurs webservices
  - Méthode `getAllCommands($limit, $offset, $filters)` supporte les filtres: `userid`, `statut`, `deleted`, `campaignid`, `type`, `crm_userid`
    - Note: si `statut = 'credit_over'`, le filtrage est appliqué en post-traitement selon le solde utilisateur (dépassé)
  - Méthode `getUserCommands($userId)` retourne une structure normalisée incluant `products`, `reference`, `webservices`, `status`, `deleted`

## Contrôleurs & Routes associés

- __Controllers\\Api\\LeadValidationController__ (`src/app/Controllers/Api/LeadValidationController.php`)
  - Actions: `run()`, `loadPendingLeads()`
  - Routes: définies dans `src/public/index.php`

- __Controllers\\AdminController__ (`src/app/Controllers/AdminController.php`)
  - Actions pertinentes:
    - `userlist`, `useradd`
    - `clientcampaignList` (liste des campagnes clients) avec actions `copy` et `delete`
    - `verificationList`, `verificationAdd`
  - Dépendances: `Services\\UserService`, `Services\\UserCampaignService`, `Services\\Validation\\ValidationMessageService`

Références de routing dans `src/public/index.php`:

```php
$App->all("/api/v2/validation/run")->setAction("Api\\LeadValidationController@run");
$App->all("/api/v2/validation/pending")->setAction("Api\\LeadValidationController@loadPendingLeads");
$App->all("/admin/clientcampaign/list")->setAction("AdminController@clientcampaignList");
$App->all("/admin/verification/list")->setAction("AdminController@verificationList");
$App->all("/admin/verification/{id}")->setAction("AdminController@verificationAdd");
```

## License

 2025 Compleo Agency. Tous droits réservés. @COMPLEOAGENCY
