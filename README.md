# CRM Compleo 🚀

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![Framework](https://img.shields.io/badge/Framework-Compleo-orange.svg)](https://github.com/COMPLEOAGENCY/Framework)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)]()

> Une application CRM moderne et puissante construite avec le Framework Compleo

## 📋 Présentation

Le CRM Compleo est une solution complète de gestion de la relation client, développée avec le Framework Compleo. Cette application PHP moderne suit une architecture MVC (Modèle-Vue-Contrôleur) et intègre les meilleures pratiques de développement.

Pour plus de détails sur le framework utilisé, consultez la [documentation du Framework Compleo](https://github.com/COMPLEOAGENCY/Framework).

### ✨ Caractéristiques Principales

- ⚡️ Application PHP 8.1+
- 🏗️ Architecture MVC
- 👥 Gestion avancée des utilisateurs et des rôles
- 🚄 Système de cache intégré
- 🌍 Support multi-langues
- 📚 API REST documentée
- 💅 Interface utilisateur moderne
- ✅ Système de validation robuste
- 🔒 Gestion des sessions sécurisée

### 🛠️ Technologies Clés

- **PHP 8.1+** - Langage de base
- **Framework Compleo** - Framework principal
- **Illuminate** - Composants Laravel
- **Redis** - Système de cache
- **Swagger** - Documentation API
- **Symfony** - Composants divers
- **Bugsnag** - Gestion des erreurs
- **Clockwork** - Debugging

## 📑 Table des Matières

### Architecture
- [Structure du Projet](#structure-du-projet)
- [Framework Compleo](#framework-compleo)
- [Patterns de Conception](#patterns-de-conception)

### Composants Principaux
- [Système d'Authentication](#système-dauthentication)
- [Gestion des Utilisateurs](#gestion-des-utilisateurs)
- [Gestion des Sessions](#gestion-des-sessions)
- [Système de Cache](#système-de-cache)
- [Validation des Données](#validation-des-données)
- [Gestion des Messages](#gestion-des-messages)

### Base de Données
- [Structure des Modèles](#structure-des-modèles)
- [Relations](#relations)
- [Migrations](#migrations)
- [Observers](#observers)

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

## 📝 License

© 2025 Compleo Agency. Tous droits réservés.
