#  ContentGenius API

**API SaaS de génération de contenu intelligent propulsée par ChatGPT**

ContentGenius est une API backend SaaS conçue pour aider les créateurs de contenu (blogueurs, marketeurs, entrepreneurs) à automatiser la génération, l'amélioration et l'analyse de contenus textuels via l'intelligence artificielle.

---

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Endpoints API](#-endpoints-api)
- [Exemples d'utilisation](#-exemples-dutilisation)
- [Système de crédits](#-système-de-crédits)
- [Jobs & Queues](#-jobs--queues)
- [Tests](#-tests)
- [Licence](#-licence)

---

##  Fonctionnalités

### Fonctionnalités utilisant ChatGPT (5)

| Fonctionnalité                 | Description                                                | Crédits |
| ------------------------------ | ---------------------------------------------------------- | ------- |
| 📝 **Génération d'articles**   | Génère des articles de blog structurés à partir d'un sujet | 3       |
| ✏️ **Réécriture de texte**     | Reformule un texte avec un ton différent                   | 2       |
| 📊 **Résumé de texte**         | Résume des textes longs en points clés                     | 1       |
| 📧 **Génération d'emails**     | Crée des emails professionnels personnalisés               | 2       |
| 🌍 **Traduction intelligente** | Traduit avec adaptation culturelle                         | 2       |

### Autres fonctionnalités (4)

| Fonctionnalité            | Description                                      |
| ------------------------- | ------------------------------------------------ |
|    **Authentification**   | Inscription, connexion, gestion tokens (Sanctum) |
|    **Gestion du profil**  | Voir/modifier profil, consulter crédits          |
|    **Historique**         | Consulter tous les contenus générés              |
|    **Système de crédits** | Gestion des quotas utilisateurs                  |

---

## 🏗 Architecture

```
app/
├── Enums/
│   ├── ContentStatus.php      # États: pending, processing, completed, failed
│   └── ContentType.php        # Types: article, rewrite, summary, email, translation
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── ProfileController.php
│   │   ├── ArticleController.php
│   │   ├── RewriteController.php
│   │   ├── SummaryController.php
│   │   ├── EmailController.php
│   │   ├── TranslationController.php
│   │   └── ContentHistoryController.php
│   ├── Requests/              # Form Requests pour validation
│   └── Resources/             # API Resources pour transformation
├── Jobs/
│   ├── GenerateArticleJob.php
│   ├── RewriteTextJob.php
│   ├── SummarizeTextJob.php
│   ├── GenerateEmailJob.php
│   └── TranslateTextJob.php
├── Models/
│   ├── User.php
│   └── ContentGeneration.php
├── Services/
│   ├── OpenAI/
│   │   └── OpenAIService.php  # Client HTTP pour OpenAI
│   ├── Content/
│   │   ├── ArticleService.php
│   │   ├── RewriteService.php
│   │   ├── SummaryService.php
│   │   ├── EmailService.php
│   │   └── TranslationService.php
│   └── CreditService.php
└── Traits/
    └── ApiResponse.php        # Réponses JSON standardisées
```

### Principes d'architecture

- **Controllers légers** : Délèguent la logique aux Services
- **Services métier** : Contiennent toute la logique business
- **Jobs asynchrones** : Tous les appels OpenAI passent par des Jobs
- **Clean Code** : Noms explicites, méthodes courtes, Single Responsibility

---

## 🛠 Installation

### Prérequis

- PHP 8.2+
- Composer
- SQLite (ou MySQL/PostgreSQL)
- Clé API OpenAI

### Étapes d'installation

```bash
# Cloner le repository
git clone https://git@github.com:Romanuis/Test-NerdX-Digital.git
cd content-genius-api

# Installer les dépendances
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Créer la base de données SQLite
touch database/database.sqlite

# Exécuter les migrations
php artisan migrate

# Lancer le serveur
php artisan serve
```

### Lancer le worker de queue

```bash
# Dans un terminal séparé
php artisan queue:work
```

---

## ⚙️ Configuration

### Variables d'environnement (.env)

```env
# OpenAI Configuration (REQUIS)
OPENAI_API_KEY=sk-votre-cle-api
OPENAI_MODEL=gpt-4o-mini
OPENAI_MAX_TOKENS=2000
OPENAI_TEMPERATURE=0.7
OPENAI_TIMEOUT=60

# Queue (pour les Jobs)
QUEUE_CONNECTION=database
```

---

## 🌐 Endpoints API

### Base URL

```
http://localhost:8000/api/v1
```

### Authentification

| Méthode | Endpoint         | Description        | Auth |
| ------- | ---------------- | ------------------ | ---- |
| POST    | `/auth/register` | Inscription        | ❌   |
| POST    | `/auth/login`    | Connexion          | ❌   |
| POST    | `/auth/logout`   | Déconnexion        | ✅   |
| GET     | `/auth/me`       | Utilisateur actuel | ✅   |

### Profil & Crédits

| Méthode | Endpoint           | Description        | Auth |
| ------- | ------------------ | ------------------ | ---- |
| GET     | `/profile`         | Voir le profil     | ✅   |
| PUT     | `/profile`         | Modifier le profil | ✅   |
| GET     | `/profile/credits` | Voir les crédits   | ✅   |

### Génération de contenu (ChatGPT)

| Méthode | Endpoint               | Description            | Auth | Crédits |
| ------- | ---------------------- | ---------------------- | ---- | ------- |
| POST    | `/articles`            | Générer un article     | ✅   | 3       |
| GET     | `/articles`            | Lister les articles    | ✅   | -       |
| GET     | `/articles/{uuid}`     | Voir un article        | ✅   | -       |
| POST    | `/rewrites`            | Réécrire un texte      | ✅   | 2       |
| GET     | `/rewrites`            | Lister les réécritures | ✅   | -       |
| GET     | `/rewrites/{uuid}`     | Voir une réécriture    | ✅   | -       |
| POST    | `/summaries`           | Résumer un texte       | ✅   | 1       |
| GET     | `/summaries`           | Lister les résumés     | ✅   | -       |
| GET     | `/summaries/{uuid}`    | Voir un résumé         | ✅   | -       |
| POST    | `/emails`              | Générer un email       | ✅   | 2       |
| GET     | `/emails`              | Lister les emails      | ✅   | -       |
| GET     | `/emails/{uuid}`       | Voir un email          | ✅   | -       |
| POST    | `/translations`        | Traduire un texte      | ✅   | 2       |
| GET     | `/translations`        | Lister les traductions | ✅   | -       |
| GET     | `/translations/{uuid}` | Voir une traduction    | ✅   | -       |

### Historique & Statistiques

| Méthode | Endpoint          | Description             | Auth |
| ------- | ----------------- | ----------------------- | ---- |
| GET     | `/history`        | Tout l'historique       | ✅   |
| GET     | `/history/stats`  | Statistiques d'usage    | ✅   |
| GET     | `/history/{uuid}` | Détail d'une génération | ✅   |

### Utilitaires

| Méthode | Endpoint     | Description        | Auth |
| ------- | ------------ | ------------------ | ---- |
| GET     | `/languages` | Langues supportées | ❌   |
| GET     | `/health`    | Health check       | ❌   |

---

## 📝 Exemples d'utilisation

### 1. Inscription

```bash
curl -X POST http://http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Roma King",
    "email": "test@test.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Réponse:**

```json
{
    "success": true,
    "message": "Registration successful. Welcome to ContentGenius!",
    "data": {
        "user": {
            "id": 1,
            "name": "Roma King",
            "email": "test@test.com",
            "credits": 100,
            "total_generations": 0
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

### 2. Générer un article

```bash
curl -X POST http://localhost:8000/api/v1/articles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "topic": "Les meilleures pratiques pour optimiser les performances Laravel en 2024",
    "tone": "professional",
    "word_count": 800
  }'
```

**Réponse (202 Accepted):**

```json
{
    "success": true,
    "message": "Article generation started. Use the UUID to check status.",
    "data": {
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "type": "article",
        "status": "pending",
        "input": {
            "text": "Les meilleures pratiques pour optimiser...",
            "parameters": {
                "tone": "professional",
                "word_count": 800
            }
        },
        "credits_used": 3,
        "created_at": "2024-01-15T10:30:00+00:00"
    }
}
```

### 3. Vérifier le statut

```bash
curl http://localhost:8000/api/v1/articles/550e8400-e29b-41d4-a716-446655440000 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Réponse (une fois terminé):**

```json
{
    "success": true,
    "data": {
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "type": "article",
        "status": "completed",
        "output": {
            "text": "# Les meilleures pratiques Laravel 2024\n\n## Introduction\n..."
        },
        "metadata": {
            "model": "gpt-4o-mini",
            "usage": {
                "prompt_tokens": 150,
                "completion_tokens": 850,
                "total_tokens": 1000
            }
        },
        "processed_at": "2024-01-15T10:30:15+00:00"
    }
}
```

### 4. Réécrire un texte

```bash
curl -X POST http://localhost:8000/api/v1/rewrites \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "text": "Notre entreprise est vraiment super et on fait plein de trucs cool.",
    "tone": "professional"
  }'
```

### 5. Traduire un texte

```bash
curl -X POST http://localhost:8000/api/v1/translations \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "text": "Bonjour, comment allez-vous aujourd hui?",
    "target_language": "en",
    "source_language": "fr"
  }'
```

---

## 💳 Système de crédits

Chaque utilisateur reçoit **100 crédits** à l'inscription.

| Type de contenu | Coût en crédits |
| --------------- | --------------- |
| Article         | 3               |
| Réécriture      | 2               |
| Résumé          | 1               |
| Email           | 2               |
| Traduction      | 2               |

### Vérification des crédits

L'API vérifie automatiquement les crédits avant chaque génération. Si l'utilisateur n'a pas assez de crédits, une erreur 402 est retournée:

```json
{
    "success": false,
    "message": "Insufficient credits. Please upgrade your plan."
}
```

---

## ⚡ Jobs & Queues

Tous les appels à l'API OpenAI sont effectués de manière **asynchrone** via des Jobs Laravel.

### Flux de traitement

```
1. POST /articles (Controller)
   ↓
2. ArticleService::create()
   - Vérifie les crédits
   - Déduit les crédits
   - Crée ContentGeneration (status: pending)
   - Dispatch GenerateArticleJob
   ↓
3. GenerateArticleJob (Queue Worker)
   - Marque status: processing
   - Appelle OpenAIService
   - Marque status: completed/failed
```

### Lancer le worker

```bash
# Development
php artisan queue:work

# Production (avec supervision)
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

---

## 🔒 Réponses JSON standardisées

Toutes les réponses suivent un format uniforme:

### Succès

```json
{
  "success": true,
  "message": "Success message",
  "data": { ... }
}
```

### Erreur

```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

### Codes HTTP utilisés

- `200` - Succès
- `201` - Ressource créée
- `202` - Requête acceptée (traitement asynchrone)
- `400` - Mauvaise requête
- `401` - Non authentifié
- `402` - Crédits insuffisants
- `404` - Ressource non trouvée
- `422` - Erreur de validation
- `500` - Erreur serveur

---

## 🧪 Tests

```bash
# Lancer tous les tests
php artisan test

# Avec couverture
php artisan test --coverage
```

---

## 📁 Structure des migrations

### users

```php
Schema::table('users', function (Blueprint $table) {
    $table->integer('credits')->default(100);
    $table->integer('total_generations')->default(0);
});
```

### content_generations

```php
Schema::create('content_generations', function (Blueprint $table) {
    $table->id();
    $table->uuid('uuid')->unique();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('type');           // article, rewrite, summary, email, translation
    $table->string('status');         // pending, processing, completed, failed
    $table->text('input_text')->nullable();
    $table->json('input_parameters')->nullable();
    $table->longText('output_text')->nullable();
    $table->json('metadata')->nullable();
    $table->text('error_message')->nullable();
    $table->integer('retry_count')->default(0);
    $table->integer('credits_used')->default(0);
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
});


## 👤 Auteur

Développé par Romanuis dans le cadre d'un test technique - API SaaS Laravel + ChatGPT

---
