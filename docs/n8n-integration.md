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

## Endpoints API disponibles

Le CRM expose trois endpoints API pour l'intégration avec N8N :

### 1. Stocker un message utilisateur

**Endpoint :** `POST /api/v2/chat/message`

**Description :** Cet endpoint permet à N8N de transmettre les messages reçus des utilisateurs (via WhatsApp, SMS, etc.) au CRM.

**Headers requis :**
- `Content-Type: application/json`
- `X-Api-Key: votre-cle-api-secrete-ici`

**Payload :**
```json
{
  "project_id": 123,
  "sender_id": 456,
  "content": "Message de l'utilisateur",
  "attachments": [] // Optionnel
}
```

**Réponse :**
```json
{
  "success": true,
  "message": "Succès",
  "status": "success",
  "conversation_id": 789,
  "message_id": 101112
}
```

### 2. Récupérer les messages à analyser

**Endpoint :** `GET /api/v2/chat/analyze`

**Description :** Cet endpoint permet à N8N de récupérer les messages qui nécessitent une analyse par l'IA.

**Headers requis :**
- `X-Api-Key: votre-cle-api-secrete-ici`

**Réponse :**
```json
{
  "success": true,
  "message": "Succès",
  "tasks": [
    {
      "task_id": "task_60a8f9e3b7c5d",
      "message_id": 101112,
      "conversation_id": 789,
      "project_id": 123,
      "project_data": {
        "id": 123,
        "title": "Titre du projet",
        "description": "Description du projet",
        "status": "en_cours"
      },
      "message_history": [
        {
          "id": 101112,
          "sender_type": "user",
          "sender_id": 456,
          "content": "Message de l'utilisateur",
          "timestamp": "2023-01-01 12:00:00",
          "is_read": false
        }
      ],
      "sender_type": "user",
      "sender_id": 456,
      "content": "Message de l'utilisateur"
    }
  ]
}
```

### 3. Stocker une réponse de l'IA

**Endpoint :** `POST /api/v2/chat/response`

**Description :** Cet endpoint permet à N8N de transmettre les réponses générées par l'IA au CRM.

**Headers requis :**
- `Content-Type: application/json`
- `X-Api-Key: votre-cle-api-secrete-ici`

**Payload :**
```json
{
  "conversation_id": 789,
  "content": "Réponse de l'IA",
  "project_id": 123, // Optionnel
  "project_updates": { // Optionnel
    "status": "qualifie",
    "budget": 5000
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

## Configuration de N8N

### Workflow de base

Voici un exemple de workflow N8N pour l'intégration avec le CRM :

1. **Réception des messages** (WhatsApp, SMS, etc.)
   - Configurer un trigger pour recevoir les messages des utilisateurs
   - Extraire les informations nécessaires (ID du projet, ID de l'expéditeur, contenu)

2. **Transmission au CRM**
   - Utiliser un nœud HTTP Request pour appeler l'endpoint `/api/v2/chat/message`
   - Configurer les headers avec la clé API
   - Formater le payload selon les exigences

3. **Récupération des messages à analyser**
   - Configurer un trigger temporisé (toutes les X minutes)
   - Utiliser un nœud HTTP Request pour appeler l'endpoint `/api/v2/chat/analyze`
   - Traiter la réponse pour extraire les tâches

4. **Analyse par l'IA**
   - Pour chaque tâche, envoyer le contenu à l'IA (OpenAI, etc.)
   - Utiliser l'historique des messages et les données du projet pour contextualiser

5. **Transmission des réponses**
   - Utiliser un nœud HTTP Request pour appeler l'endpoint `/api/v2/chat/response`
   - Inclure les mises à jour du projet si nécessaire

6. **Envoi des réponses aux utilisateurs**
   - Configurer les nœuds appropriés pour envoyer les réponses via les canaux d'origine (WhatsApp, SMS, etc.)

## Exemple de configuration N8N

Voici un exemple de configuration JSON pour un workflow N8N :

```json
{
  "nodes": [
    {
      "parameters": {
        "rule": {
          "interval": [
            {
              "field": "minutes",
              "minutesInterval": 5
            }
          ]
        }
      },
      "name": "Vérifier les nouveaux messages",
      "type": "n8n-nodes-base.scheduleTrigger",
      "typeVersion": 1,
      "position": [
        250,
        300
      ]
    },
    {
      "parameters": {
        "url": "https://votre-crm.com/api/v2/chat/analyze",
        "options": {
          "headers": {
            "X-Api-Key": "votre-cle-api-secrete-ici"
          }
        }
      },
      "name": "Récupérer les messages",
      "type": "n8n-nodes-base.httpRequest",
      "typeVersion": 1,
      "position": [
        450,
        300
      ]
    }
  ],
  "connections": {
    "Vérifier les nouveaux messages": {
      "main": [
        [
          {
            "node": "Récupérer les messages",
            "type": "main",
            "index": 0
          }
        ]
      ]
    }
  }
}
```

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
