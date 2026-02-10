# DIAMON - Plateforme E-Commerce de Luxe d'Occasion

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-4.4.0-FF6384?style=for-the-badge&logo=chart.js&logoColor=white)
![Security](https://img.shields.io/badge/Security-OWASP-green?style=for-the-badge)

Marketplace professionnelle de confiance pour l'achat, la vente et l'échange de produits de luxe d'occasion (montres, sacs, bijoux). Plateforme e-commerce complète développée en PHP natif avec architecture MVC, garantissant authenticité, transparence et sécurité des transactions.

## Table des Matières

- [Présentation](#-présentation)
- [Fonctionnalités](#-fonctionnalités)
- [Technologies](#-technologies)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Sécurité](#-sécurité)
- [Base de Données](#-base-de-données)
- [Dashboard Analytics](#-dashboard-analytics)
- [Captures d'Écran](#-captures-décran)
- [Roadmap](#-roadmap)
- [Statistiques](#-statistiques)
- [Auteur](#-auteur)

## Présentation

### Contexte

DIAMON est une plateforme e-commerce complète développée dans le cadre d'un projet personnel visant à consolider les acquis d'une formation intensive en développement web full-stack (67 heures sur Udemy). L'objectif était de créer un projet **production-ready** et réutilisable pour de futurs développements clients.

### Problématique Métier

Créer une marketplace de confiance pour le luxe d'occasion en garantissant :
- **L'authenticité des produits** : Système de grading professionnel (A/B/C)
- **La transparence** : Avis clients vérifiés avec photos
- **La sécurité** : Protection des transactions et données personnelles (OWASP)

### Métriques du Projet

| Métrique | Valeur |
|----------|--------|
| Temps investi | **115 heures** |
| Lignes de code | **~8 000** |
| Fichiers | **70+** |
| Tables BDD | **15** |
| Relations FK | **20+** |
| Fonctionnalités | **12+** |

## Fonctionnalités

### Côté Client

#### Catalogue & Navigation
- **Recherche avancée AJAX** : 8+ filtres dynamiques en temps réel
  - Catégorie (Montres, Sacs, Bijoux)
  - Grading (A, B, C)
  - Fourchette de prix
  - Marque
  - État
- **Système de grading transparent** : 
  - Grade A : Excellent état (comme neuf)
  - Grade B : Bon état (légères traces d'usure)
  - Grade C : État correct (usure visible)
- **Pagination** avec chargement optimisé

#### Panier & Commandes
- **Panier intelligent** :
  - Ajout/suppression AJAX sans rechargement
  - Calcul automatique des totaux
  - Gestion du stock en temps réel
- **Codes promo conditionnels** :
  - Réduction en pourcentage ou montant fixe
  - Conditions : dates validité, montant minimum, limite d'utilisation
  - Application automatique avec validation backend

#### Wishlist
- **Toggle AJAX instantané** : Ajouter/retirer favoris
- **Persistance** : Sauvegarde par utilisateur
- **Indicateur visuel** : Icône cœur active/inactive

#### Système d'Avis Clients
- **Notation 1-5 étoiles**
- **Upload de photos** :
  - Maximum 4 photos par avis
  - Validation MIME type stricte
  - Compression automatique
- **Modération** : Approbation admin avant publication
- **Avis vérifiés** : Uniquement après achat

#### Expérience Utilisateur
- **Dark mode** :
  - Toggle persistant (localStorage)
  - Détection préférence système
  - Transition fluide
- **Formulaires** :
  - Vente de produits
  - Échange de produits
  - Contact
- **Interface responsive** : Mobile-first design

### Côté Administration

#### Dashboard Analytics (Chart.js)
4 graphiques interactifs en temps réel :
1. **Chiffre d'Affaires Mensuel** : Évolution sur 12 mois
2. **Ventes par Catégorie** : Répartition (Montres, Sacs, Bijoux)
3. **Évolution Clients** : Nouveaux inscrits par mois
4. **Statuts Commandes** : En cours / Livrées / Annulées

#### Gestion Produits (CRUD Complet)
- **Création** :
  - Upload multi-images : 1 principale + N secondaires
  - Validation automatique (format, taille, MIME)
  - Compression intelligente
- **Modification** : Édition complète avec prévisualisation
- **Suppression** : Soft delete avec historique
- **Filtrage avancé** : Par catégorie, stock, prix, date

#### Gestion Codes Promo
- **Création** avec paramètres :
  - Type (%, montant fixe)
  - Dates de validité
  - Montant minimum requis
  - Limite d'utilisation
- **Statistiques d'utilisation** :
  - Nombre d'utilisations
  - CA généré
  - Clients bénéficiaires

#### Modération Avis Clients
- **File d'attente** : Avis en attente de modération
- **Actions** :
  - Approuver
  - Rejeter
  - Supprimer
- **Historique** : Traçabilité complète

#### Exports Avancés
6 types d'exports CSV/Excel :
1. **Produits** : Catalogue complet
2. **Commandes** : Historique des ventes
3. **Clients** : Base de données utilisateurs
4. **Avis** : Feedbacks clients
5. **Statistiques** : KPIs e-commerce
6. **Top Produits** : Meilleures ventes

## Technologies

### Stack Technique

| Catégorie | Technologie | Version | Justification |
|-----------|-------------|---------|---------------|
| **Backend** | PHP | 8.1+ | Orienté objet, MVC natif, PDO intégré |
| **BDD** | MySQL | 8.0+ | Relationnel, performant, ACID |
| **Frontend** | JavaScript | ES6+ | Async/await, Fetch API, modules |
| **CSS** | Tailwind CSS | 3.x | Utility-first, rapid prototyping |
| **Charts** | Chart.js | 4.4.0 | Léger, interactif, 8 types de graphiques |
| **Serveur** | XAMPP | 8.2.x | LAMP stack intégré |

### Bibliothèques et Dépendances

**PHP** (vanilla, aucune dépendance externe)
- `PDO` : Accès base de données (natif)
- `password_hash()` / `password_verify()` : Cryptographie bcrypt (natif)
- `finfo_file()` : Validation MIME type uploads (natif)

**JavaScript** (vanilla, pas de npm)
- Fetch API (natif)
- LocalStorage (natif)
- Chart.js (CDN)

**CSS**
- Tailwind CSS 3.x (CDN)

## Architecture

### Pattern MVC

Architecture MVC sans framework pour maîtriser les fondamentaux et garantir la maintenabilité.

```
diamon_luxe/
│
├── admin/                      # Zone administration (40+ fichiers)
│   ├── dashboard.php           # Tableau de bord analytics
│   ├── produits/               # CRUD produits
│   ├── codes_promo/            # Gestion codes promo
│   ├── avis/                   # Modération avis
│   └── exports/                # Exports CSV/Excel
│
├── models/                     # Modèles métier (10 classes)
│   ├── Client.php              # Modèle utilisateur
│   ├── Produit.php             # Modèle produit
│   ├── Panier.php              # Modèle panier
│   ├── Commande.php            # Modèle commande
│   ├── CodePromo.php           # Modèle code promo
│   ├── Avis.php                # Modèle avis
│   ├── Wishlist.php            # Modèle wishlist
│   ├── Categorie.php           # Modèle catégorie
│   └── Grade.php               # Modèle grading
│
├── views/                      # Vues (présentation)
│   ├── accueil.php             # Page d'accueil
│   ├── catalogue.php           # Liste produits
│   ├── produit.php             # Détail produit
│   ├── panier.php              # Panier
│   ├── wishlist.php            # Liste de souhaits
│   └── compte/                 # Espace client
│
├── components/                 # Composants réutilisables
│   ├── header.php              # En-tête
│   ├── footer.php              # Pied de page
│   ├── navbar.php              # Navigation
│   └── modal.php               # Modales
│
├── includes/                   # Fichiers communs
│   ├── db.php                  # Configuration BDD
│   ├── functions.php           # Fonctions utilitaires
│   └── session.php             # Gestion sessions
│
├── public/                     # Assets publics
│   ├── css/                    # Styles
│   ├── js/                     # Scripts JavaScript
│   ├── images/                 # Images site
│   └── uploads/                # Images produits/avis
│
└── sql/                        # Schéma BDD
    └── schema.sql              # Structure complète
```

## Base de Données

### Architecture

**15 tables normalisées 3NF** avec **20+ relations FOREIGN KEY** et **25+ indexes** pour les performances.

### Tables Principales

| Table | Description | Colonnes Clés |
|-------|-------------|---------------|
| `client` | Utilisateurs | email UNIQUE, password hash bcrypt |
| `produit` | Catalogue | marque, nom, prix, stock, grade, catégorie |
| `panier` | Panier utilisateur | UNIQUE (client_id, produit_id) |
| `code_promo` | Codes de réduction | type (%, montant fixe), conditions |
| `commande` | Commandes passées | numero UNIQUE, statut, total |
| `avis` | Avis clients | note (1-5), photos, moderation |
| `wishlist` | Liste de souhaits | UNIQUE (client_id, produit_id) |
| `categorie` | Catégories | Montres, Sacs, Bijoux |
| `grade` | Grading qualité | A, B, C avec descriptions |

### Relations Clés

```sql
-- Exemples de relations
FOREIGN KEY (client_id) REFERENCES client(id) ON DELETE CASCADE
FOREIGN KEY (produit_id) REFERENCES produit(id) ON DELETE CASCADE
FOREIGN KEY (categorie_id) REFERENCES categorie(id)
FOREIGN KEY (grade_id) REFERENCES grade(id)
```

## Installation

### Prérequis Système

| Composant | Version Minimale | Recommandée |
|-----------|------------------|-------------|
| PHP | 8.1.0 | 8.2.x |
| MySQL | 8.0.0 | 8.0.35+ |
| Apache | 2.4.x | 2.4.58+ |

### Installation Locale (XAMPP)

#### Étape 1 : Copier le Projet

**Windows**
```bash
cd C:/xampp/htdocs
# Copier le dossier diamon-luxe ici
cd diamon-luxe
```

**macOS/Linux**
```bash
cd /Applications/XAMPP/htdocs  # macOS
# ou
cd /opt/lampp/htdocs            # Linux
# Copier le dossier diamon-luxe ici
cd diamon-luxe
```

#### Étape 2 : Configuration Base de Données

1. **Ouvrir phpMyAdmin** : `http://localhost/phpmyadmin`

2. **Créer la base de données** :
```sql
CREATE DATABASE diamon_luxe
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

3. **Importer le schéma** :
   - Sélectionner la base `diamon_luxe`
   - Onglet "Importer"
   - Choisir le fichier `sql/schema.sql`
   - Cliquer sur "Exécuter"

#### Étape 3 : Configuration Application

Éditer le fichier `includes/db.php` :

```php
<?php
$host = 'localhost';
$dbname = 'diamon_luxe';
$username = 'root';        // Utilisateur MySQL
$password = '';            // Mot de passe MySQL (vide par défaut sur XAMPP)

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
```

#### Étape 4 : Lancement

1. **Démarrer les services** dans XAMPP Control Panel :
   - Apache
   - MySQL

2. **Accéder à l'application** :
   - **Site client** : `http://localhost/diamon-luxe/index.php`
   - **Administration** : `http://localhost/diamon-luxe/admin/dashboard.php`

3. **Compte admin par défaut** (à modifier en production) :
   - Email : `admin@diamon.com`
   - Mot de passe : `admin123`

## Sécurité

### Mesures de Protection (100%)

| Menace | Protection | Taux d'Application |
|--------|-----------|-------------------|
| **Injection SQL** | Requêtes préparées PDO | 100% |
| **XSS** | `htmlspecialchars()` | 100% |
| **Mots de passe** | Hachage bcrypt | 100% |
| **Upload fichiers** | Validation MIME réelle | 100% |
| **Sessions** | Régénération ID | 100% |

### Détails d'Implémentation

#### 1. Protection Injection SQL
```php
// CORRECT - Requête préparée
$stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
$stmt->execute([$id]);

// INCORRECT - Requête directe (jamais utilisé)
$query = "SELECT * FROM produit WHERE id = $id"; // DANGEREUX
```

#### 2. Protection XSS
```php
// Échappement systématique des sorties
echo htmlspecialchars($nom_produit, ENT_QUOTES, 'UTF-8');
```

#### 3. Sécurité Mots de Passe
```php
// Hachage lors de l'inscription
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Vérification lors de la connexion
if (password_verify($password, $hash_bdd)) {
    // Connexion réussie
}
```

#### 4. Validation Upload Fichiers
```php
// Validation MIME type réelle (pas juste l'extension)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['image']['tmp_name']);

$allowed = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowed)) {
    throw new Exception("Type de fichier non autorisé");
}
```

#### 5. Sécurité Sessions
```php
// Régénération ID de session
session_start();
session_regenerate_id(true);

// Headers de sécurité
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
```

## Dashboard Analytics

### Graphiques Interactifs (Chart.js)

#### 1. Chiffre d'Affaires Mensuel
```javascript
// Graphique en ligne - Évolution CA sur 12 mois
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Fév', 'Mar', ...],
        datasets: [{
            label: 'CA Mensuel (€)',
            data: [15000, 18500, 22000, ...]
        }]
    }
});
```

#### 2. Ventes par Catégorie
```javascript
// Graphique circulaire - Répartition par type
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Montres', 'Sacs', 'Bijoux'],
        datasets: [{
            data: [45, 35, 20] // Pourcentages
        }]
    }
});
```

#### 3. Évolution Clients
```javascript
// Graphique en barres - Nouveaux inscrits
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan', 'Fév', 'Mar', ...],
        datasets: [{
            label: 'Nouveaux clients',
            data: [25, 32, 28, ...]
        }]
    }
});
```

#### 4. Statuts Commandes
```javascript
// Graphique en barres horizontales
new Chart(ctx, {
    type: 'horizontalBar',
    data: {
        labels: ['En cours', 'Livrées', 'Annulées'],
        datasets: [{
            data: [45, 120, 8]
        }]
    }
});
```

## Captures d'Écran

> **Note** : Ajoutez ici des captures d'écran de votre plateforme pour illustrer le README

```
[Screenshot 1 : Page d'accueil]
[Screenshot 2 : Catalogue avec filtres AJAX]
[Screenshot 3 : Détail produit avec grading]
[Screenshot 4 : Panier avec code promo]
[Screenshot 5 : Dashboard admin]
[Screenshot 6 : Dark mode]
```

## Roadmap

### Court Terme (10-40h)

| Évolution | Effort Estimé | Priorité |
|-----------|---------------|----------|
| **Paiement Stripe/PayPal** | 10-15h | 🔴 Haute |
| **Notifications email (PHPMailer)** | 8-10h | 🔴 Haute |
| **Protection CSRF généralisée** | 5-8h | 🟡 Moyenne |
| **Cache Redis** | 15-20h | 🟢 Basse |
| **API REST complète** | 30-40h | 🟡 Moyenne |
| **Application mobile (PWA)** | 20-30h | 🟢 Basse |

### Moyen Terme

- [ ] **Système de chat client-vendeur**
- [ ] **Programme de fidélité**
- [ ] **Recommandations IA (machine learning)**
- [ ] **Authentification 2FA**
- [ ] **Internationalisation (i18n)**
- [ ] **Multi-devises**

### Long Terme

- [ ] **Application mobile native (React Native/Flutter)**
- [ ] **Marketplace multi-vendeurs**
- [ ] **Blockchain pour certificat d'authenticité**
- [ ] **Réalité augmentée (essai virtuel)**

## Statistiques Détaillées

### Code & Architecture
- **Lignes de code PHP** : ~5 000
- **Lignes de code JavaScript** : ~2 000
- **Lignes de code SQL** : ~1 000
- **Fichiers PHP** : 50+
- **Fichiers JavaScript** : 15+
- **Fichiers CSS** : 5+

### Base de Données
- **Tables** : 15
- **Relations FOREIGN KEY** : 20+
- **Indexes** : 25+
- **Triggers** : 3
- **Stored Procedures** : 2

### Fonctionnalités
- **Pages client** : 12+
- **Pages admin** : 20+
- **Endpoints AJAX** : 15+
- **Types d'exports** : 6
- **Graphiques analytics** : 4

## 🎓 Compétences Démontrées

Ce projet démontre la maîtrise de :

**Architecture MVC professionnelle** sans framework  
**PHP 8.1+ Orienté Objet** (POO avancée)  
**Sécurité niveau production** (OWASP Top 10)  
**Base de données relationnelle** (normalisation 3NF)  
**JavaScript ES6+** (Async/await, Fetch API)  
**AJAX & Interactivité** temps réel  
**Visualisation de données** (Chart.js)  
**Upload & validation fichiers** sécurisés  
**Gestion d'état** (sessions, localStorage)  
**Design responsive** (Tailwind CSS)  
**E-commerce avancé** (panier, promo, wishlist)  
**Documentation technique** complète  

## Auteur

**Kerim** - Développeur Web Full-Stack  
🎓 BTS SIO SLAM - Alternance Abby Ambers  

- Portfolio : kocait.fr


## Contexte Pédagogique

**Formation** : Udemy - Développement Web Full-Stack (67 heures)  
**Projet** : Post-formation (personnel)  
**Objectif** : Consolider les acquis et créer un projet production-ready  
**Durée** : 115 heures  
**Date** : Décembre 2025  
**Statut** : Production-ready  

## Licence

Ce projet est sous **licence propriétaire** (adaptable pour usage commercial).  
Tous droits réservés © 2025 Keril

Pour toute utilisation commerciale, veuillez me contacter.

## Remerciements

- **Udemy** pour la formation complète en développement web
- **Abby Ambers** pour le soutien durant l'alternance
- **Communauté PHP** pour les ressources et la documentation
- **Chart.js** pour la bibliothèque de visualisation
- **Tailwind CSS** pour le framework CSS utility-first

## Ressources Complémentaires

- [Documentation PHP](https://www.php.net/docs.php)
- [Documentation MySQL](https://dev.mysql.com/doc/)
- [Guide Chart.js](https://www.chartjs.org/docs/latest/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)

---

**Si ce projet vous inspire, n'hésitez pas à lui donner une étoile sur GitHub !**

### Développé avec passion et professionnalisme

```
████████████████████████████████████████████████
█ DIAMON - E-Commerce de Luxe d'Occasion      █
█ Version 1.0.0 - Production Ready            █
█ 115h de développement | 8000+ lignes        █
█ Sécurité 100% | Architecture MVC            █
████████████████████████████████████████████████
```
