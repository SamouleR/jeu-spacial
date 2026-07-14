# 🚀 Jeu Spatial

Un jeu web d'aventure et de stratégie spatiale interactif. Développé en **PHP**, **JavaScript**, et **HTML/CSS**, ce projet vous plonge dans la gestion d'une flotte spatiale avec des rôles uniques.

## 🌟 Fonctionnalités

- **Gestion de flotte** : Contrôlez différents types d'unités comme des Vaisseaux et des Drones.
- **Rôles spécialisés** : Incarnez des personnages aux capacités distinctes (ex: Mentaliste, Opérateur).
- **Interface dynamique** : Un tableau de bord interactif géré avec JavaScript (`board.js`).
- **Sauvegarde et Données** : Gestion de l'état de la partie via PHP et des fichiers JSON/dat.
- **Modes de jeu multiples** : Différentes approches et configurations pour vos parties.

## 🛠️ Technologies utilisées

- **Backend** : PHP (Programmation Orientée Objet)
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Données** : JSON

## 📂 Structure du projet

- `Joueur.php`, `Personne.php` : Classes représentant les acteurs du jeu.
- `Vaisseau.php`, `Drone.php` : Entités contrôlables dans l'espace.
- `Mentaliste.php`, `Operateur.php` : Rôles spécifiques avec leurs attributs.
- `Partie.php`, `Partie2.php` : Moteurs de gestion de l'état de la partie.
- `board.js`, `gestion.js` : Logique d'interface et d'interaction côté client.
- `index.html`, `mode-jeu.html`, `rapport.html` : Vues et interfaces utilisateur.

## 🚀 Installation & Utilisation

1. Clonez ce dépôt sur votre machine locale :
   ```bash
   git clone https://github.com/SamouleR/jeu-spacial.git
   ```
2. Placez les fichiers dans le répertoire de votre serveur web local (ex: `htdocs` pour XAMPP, `www` pour WAMP).
3. Accédez au projet via votre navigateur en pointant sur le dossier du jeu.