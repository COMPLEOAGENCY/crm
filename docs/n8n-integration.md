# Documentation d'intégration N8N avec le CRM Compleo

Cette documentation explique comment intégrer N8N avec le CRM Compleo pour la qualification automatique des projets via une IA.

## Prérequis

- Une instance N8N fonctionnelle (version 0.170.0 ou supérieure)
- Accès au CRM Compleo
- Une clé API configurée dans le fichier d'environnement du CRM

## Configuration du CRM

1. Assurez-vous que les variables d'environnement suivantes sont configurées dans le fichier `dev.env` (ou `.env` en production) :

```
N8N_API_KEY="votre-cle-api-secrete-ici"
N8N_BASE_URL="http://localhost:5678"
N8N_QUEUE_PREFIX="n8n:"
N8N_PROCESSING_TIMEOUT="300"
```

2. Remplacez `votre-cle-api-secrete-ici` par une clé API sécurisée et `http://localhost:5678` par l'URL de votre instance N8N.

3. Exécutez le script SQL suivant pour ajouter la colonne `is_processed` à la table `chat_message` :

```sql
-- Ajout de la colonne is_processed à la table chat_message
ALTER TABLE chat_message ADD COLUMN is_processed TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Indique si le message a été traité par l\'IA';

-- Mise à jour des messages existants comme étant déjà traités
UPDATE chat_message SET is_processed = 1 WHERE 1;

-- Création d'un index pour optimiser les requêtes de recherche de messages non traités
ALTER TABLE chat_message ADD INDEX idx_is_processed (is_processed);
```

## Endpoints API disponibles

Le CRM expose trois endpoints API pour l'intégration avec N8N :

### 1. Envoyer un message utilisateur

**Endpoint :** `POST /api/ai-chat/message`

**Description :** Cet endpoint permet à N8N d'envoyer un message d'un lead au CRM.

**Authentification :** Clé API requise dans l'en-tête `X-API-KEY`

**Payload :**

```json
{
  "phone": "+33612345678",         // Obligatoire: Numéro de téléphone du lead
  "content": "Texte du message",   // Obligatoire: Contenu du message
  "first_name": "Jean",            // Optionnel: Prénom du lead
  "last_name": "Dupont",           // Optionnel: Nom du lead
  "email": "jean@example.com",     // Optionnel: Email du lead
  "attachments": []                // Optionnel: Pièces jointes (URLs)
}
```

**Réponse :**

```json
{
  "success": true,
  "message": "Succès",
  "status": "success",
  "conversation_id": 123,
  "message_id": 456
}
```

### 2. Récupérer les messages à analyser

**Endpoint :** `GET /api/ai-chat/messages-to-analyze`

**Description :** Cet endpoint permet à N8N de récupérer les messages non traités pour analyse par l'IA.

**Authentification :** Clé API requise dans l'en-tête `X-API-KEY`

**Réponse :**

```json
{
  "success": true,
  "message": "Succès",
  "tasks": [
    {
      "task_id": "task_5f8a4b2c3d",
      "message_id": 456,
      "conversation_id": 123,
      "lead_id": 789,
      "lead_data": {
        "id": 789,
        "first_name": "Jean",
        "last_name": "Dupont",
        "phone": "+33612345678"
      },
      "projects": [
        {
          "id": 101,
          "title": "Rénovation salle de bain",
          "status": "en_cours"
        }
      ],
      "message_history": [
        {
          "id": 455,
          "sender_type": "system",
          "content": "Bonjour, comment puis-je vous aider ?",
          "timestamp": "2025-03-23T10:30:00Z"
        },
        {
          "id": 456,
          "sender_type": "lead",
          "content": "J'ai une question sur mon projet",
          "timestamp": "2025-03-23T10:35:00Z"
        }
      ],
      "content": "J'ai une question sur mon projet"
    }
  ]
}
```

### 3. Envoyer une réponse de l'IA

**Endpoint :** `POST /api/ai-chat/ai-response`

**Description :** Cet endpoint permet à N8N d'envoyer une réponse générée par l'IA.

**Authentification :** Clé API requise dans l'en-tête `X-API-KEY`

**Payload :**

```json
{
  "conversation_id": 123,
  "content": "Voici la réponse de l'IA",
  "identified_project_id": 101,    // Optionnel: ID du projet identifié par l'IA
  "project_updates": {             // Optionnel: Mises à jour du projet
    "status": "qualifie",
    "custom_fields": {
      "budget": "10000"
    }
  }
}
```

**Réponse :**

```json
{
  "success": true,
  "message": "Succès",
  "status": "success"
}
```

## Algorithme d'association des messages aux projets

Lorsqu'un message est reçu avec uniquement un numéro de téléphone, le système utilise l'algorithme suivant pour déterminer à quel projet il est associé :

1. Recherche du lead par numéro de téléphone
2. Si un seul projet est associé au lead, pas d'ambiguïté
3. Si plusieurs projets sont associés au lead :
   - Chercher celui avec un message sans réponse du lead
   - Sinon, utiliser le projet le plus récent
4. Si aucun projet n'est trouvé, créer une conversation "inbox" pour le lead

## Structure des conversations

Les conversations sont identifiées par un slug unique :

- `project-{projectId}-main` : Conversation principale d'un projet
- `lead-{leadId}-inbox` : Boîte de réception pour un lead sans projet associé

## Traitement des messages

1. N8N intercepte les messages des utilisateurs (WhatsApp, SMS, etc.)
2. N8N envoie le message au CRM via l'API
3. Le CRM détermine le projet associé et stocke le message
4. N8N récupère les messages non traités
5. L'IA analyse les messages et génère des réponses
6. N8N envoie les réponses au CRM et aux utilisateurs

## Configuration de N8N

Pour configurer N8N :

1. Créer un workflow pour intercepter les messages
2. Configurer un nœud HTTP Request pour envoyer les messages au CRM
3. Configurer un nœud HTTP Request pour récupérer les messages à analyser
4. Configurer un nœud pour l'analyse par l'IA
5. Configurer un nœud HTTP Request pour envoyer les réponses au CRM
6. Configurer un nœud pour envoyer les réponses aux utilisateurs

## Sécurité

- Toutes les requêtes API doivent inclure une clé API valide dans l'en-tête `X-API-KEY`
- Les communications entre N8N et le CRM doivent être chiffrées (HTTPS)
- Les numéros de téléphone doivent être normalisés avant traitement

## Bonnes pratiques

1. **Sécurité** : Assurez-vous que la clé API est sécurisée et n'est pas exposée publiquement.
2. **Gestion des erreurs** : Implémentez une gestion robuste des erreurs dans vos workflows N8N.
3. **Journalisation** : Activez la journalisation pour suivre les échanges entre N8N et le CRM.
4. **Tests** : Testez l'intégration avec des données fictives avant de la déployer en production.
5. **Surveillance** : Mettez en place une surveillance pour détecter les problèmes d'intégration.

## Dépannage

### Problèmes courants

1. **Erreur 401 Unauthorized** : Vérifiez que la clé API est correcte et qu'elle est incluse dans les headers.
2. **Erreur 400 Bad Request** : Vérifiez que le payload est correctement formaté et contient tous les champs requis.
3. **Aucune réponse de l'IA** : Vérifiez que l'IA est correctement configurée et qu'elle reçoit les données nécessaires.

### Support

Pour toute question ou problème, contactez l'équipe de support du CRM Compleo.
