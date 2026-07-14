<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Spacialship</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="shortcut icon" href="assets/logo/image.png" type="image/x-icon">
    <style>
        /* --- AJOUT DE LA POLICE TERBAANG --- */
        @font-face {
            font-family: 'Terbaang';
            /* Assure-toi que le nom du fichier correspond exactement (maj/min) */
            /* Si c'est un .ttf, change l'extension ci-dessous */
            src: url('Terbaang.ttf') format('opentype'); 
            font-weight: normal;
            font-style: normal;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            background: black;
        }

        #myVideo {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: contain; /* 'cover' est souvent mieux pour un fond d'écran pour éviter les bandes noires */
            background: black;
        }

        .start-title {
            /* --- APPLICATION DE LA POLICE --- */
            font-family: 'Terbaang', sans-serif; 
            font-size: 8rem;
            color: white;
            /* Optionnel : Ajout d'une ombre pour la lisibilité sur la vidéo */
            text-shadow: 0 0 10px rgba(0,0,0,0.7);
            margin-bottom: 0; /* Évite un trop grand espace sous le titre */
        }

        .launch-button {
            background-color: #ffffff;
            color: #000000;
            border: none;
            padding: 15px 30px;
            font-size: 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 20px;
            font-family: sans-serif; /* Garder le bouton lisible, ou mettre aussi 'Terbaang' si tu veux */
            transition: background-color 0.3s ease; /* Animation douce au survol */
        }

        .launch-button:hover {
            background-color: #b9b9b9ff;
            color: #000000ff;
        }
    </style>
</head>

<body>
    <video autoplay muted loop id="myVideo" style="object-fit: cover;">
        <source src="/video/planete.mp4" type="video/mp4">
    </video>

    <audio id="background-audio" src="assets/audio/space-sound.mp3" autoplay loop muted></audio>

    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10; text-align: center; width: 100%;">
        
        <h1 class="start-title">SpaceWar</h1>
        <button id="launchGameButton" class="launch-button">Lancer le jeu</button>
    </div>

    <script src="scripts/choix-joueur.js" defer></script>
    <script src="scripts/taille-ecran.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var audio = document.getElementById('background-audio');
            var launchButton = document.getElementById('launchGameButton');

            function playAndUnmuteAudio() {
                audio.muted = false;
                var playPromise = audio.play();
                if (playPromise !== undefined) {
                    playPromise.catch(function(error) {
                        console.error("Échec de la lecture audio sur interaction utilisateur:", error);
                    });
                }
            }

            var initialPlayPromise = audio.play();
            if (initialPlayPromise !== undefined) {
                initialPlayPromise.then(function() {
                    console.log("Lecture audio automatique démarrée (muette).");
                }).catch(function(error) {
                    console.error("Échec de la lecture audio automatique:", error);
                });
            }

            launchButton.addEventListener('click', function() {
                playAndUnmuteAudio();
                // Petit délai pour entendre le son "start" si tu en ajoutes un plus tard
                setTimeout(() => {
                     window.location.href = 'choix-joueur.php';
                }, 100);
            });
        });
    </script>
</body>

</html>