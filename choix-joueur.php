<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sélection des Flottes</title>
    <style>
        /* --- GARDE TON CSS EXISTANT (IMPORT FONT, BODY, ETC) --- */
        @font-face { font-family: 'Terbaang'; src: url('assets/fonts/Terbaang.otf') format('opentype'); }
        body { margin: 0; height: 100vh; width: 100vw; overflow: hidden; background: url('assets/logo/image.png') no-repeat center center fixed; background-size: cover; background-color: #0b0d17; font-family: 'Terbaang', sans-serif; color: white; display: flex; flex-direction: column; }
        h1 { text-align: center; margin: 15px 0; text-shadow: 0 0 15px #00d2ff; letter-spacing: 4px; font-size: 2.5rem; z-index: 10; }
        .main-container { display: flex; height: 100%; width: 100%; position: relative; }
        .divider { width: 6px; background: red; box-shadow: 0 0 20px red; height: 90%; align-self: center; z-index: 5; }
        
        /* Zones Joueurs */
        .player-zone { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 10px; transition: all 0.5s ease; }
        .player-title { font-size: 1.8rem; margin-bottom: 5px; text-transform: uppercase; }
        .instruction { font-family: sans-serif; font-size: 1rem; color: #ccc; margin-bottom: 15px; font-weight: bold; }

        /* Grille & Cartes (Slider) */
        .grid-container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; width: 85%; max-height: 60vh; overflow-y: auto; padding: 10px; padding-bottom: 100px; scrollbar-width: none; }
        .grid-container::-webkit-scrollbar { display: none; }
        .card { background: rgba(0, 0, 0, 0.7); border: 3px solid #ff0000; border-radius: 15px; padding: 10px; text-align: center; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 110px; }
        .card img { width: 60px; height: 60px; object-fit: contain; margin-bottom: 8px; }
        .card.selected { border-color: #00ff00; background: rgba(0, 255, 0, 0.15); box-shadow: 0 0 20px #00ff00; }
        
        /* Bouton */
        .validate-btn { position: absolute; bottom: 30px; padding: 15px 60px; font-family: 'Terbaang', sans-serif; font-size: 1.3rem; border: 2px solid #444; background: rgba(0,0,0,0.9); color: #555; border-radius: 50px; cursor: not-allowed; transition: 0.3s; }
        .validate-btn.ready { border-color: #00ff00; background: #00ff00; color: black; box-shadow: 0 0 30px #00ff00; cursor: pointer; }

        /* --- NOUVEAU CSS POUR LE ONLINE (SINGLE SCREEN) --- */
        .hidden { display: none !important; }
        
        /* Quand un joueur joue seul sur son écran, il prend toute la place */
        .single-screen-mode { 
            flex: 0 0 100% !important; 
            max-width: 100%; 
            background: radial-gradient(circle at center, rgba(0,100,255,0.1) 0%, rgba(0,0,0,0) 70%);
        }

        /* En plein écran, on affiche 4 colonnes au lieu de 2 pour que ce soit plus beau */
        .single-screen-mode .grid-container {
            grid-template-columns: repeat(4, 1fr); 
            width: 80%;
            max-height: 70vh;
        }

        /* Indicateur de salle */
        .room-indicator {
            position: absolute;
            top: 20px; right: 20px;
            font-family: sans-serif;
            background: rgba(0,0,0,0.8);
            border: 1px solid #00d2ff;
            padding: 5px 15px;
            border-radius: 20px;
            color: #00d2ff;
            display: none; /* Caché en local */
        }
    </style>
</head>
<body>

    <div class="room-indicator" id="roomTag">SALLE: ----</div>
    <h1 id="mainTitle">ARMEMENT</h1>

    <div class="main-container">
        
        <div class="player-zone" id="p1-zone">
            <div class="player-title">JOUEUR 1</div>
            <div class="instruction" id="p1-instruction">Chargement...</div>
            <div class="grid-container" id="p1-grid"></div>
            <button class="validate-btn" id="p1-btn" onclick="validatePhase(1)">VALIDER</button>
        </div>

        <div class="divider" id="divider"></div>

        <div class="player-zone" id="p2-zone">
            <div class="player-title">JOUEUR 2</div>
            <div class="instruction" id="p2-instruction">Chargement...</div>
            <div class="grid-container" id="p2-grid"></div>
            <button class="validate-btn" id="p2-btn" onclick="validatePhase(2)">VALIDER</button>
        </div>

    </div>

    <script>
        // --- CONFIGURATION ET RECUPERATION URL ---
        const urlParams = new URLSearchParams(window.location.search);
        const MODE = urlParams.get('mode') || 'local'; // 'local' ou 'online'
        const ROLE = parseInt(urlParams.get('role')) || 0; // 1 ou 2
        const ROOM = urlParams.get('room') || null;

        const CONFIG = {
            ships: { limit: 3, name: 'Vaisseaux', key: 'vaisseaux' },
            drones: { limit: 3, name: 'Drones', key: 'drones' }
        };

        let players = {
            1: { phase: 'ships', selected: [], ready: false },
            2: { phase: 'ships', selected: [], ready: false }
        };
        let gameData = null;

        document.addEventListener('DOMContentLoaded', () => {
            
            // 1. GESTION DE L'AFFICHAGE SELON LE MODE
            if (MODE === 'online') {
                document.getElementById('roomTag').style.display = 'block';
                document.getElementById('roomTag').innerText = "SALLE: " + ROOM;
                document.getElementById('divider').classList.add('hidden'); // On cache la barre rouge

                if (ROLE === 1) {
                    // JE SUIS JOUEUR 1 : Je cache la zone J2
                    document.getElementById('p2-zone').classList.add('hidden');
                    document.getElementById('p1-zone').classList.add('single-screen-mode');
                    document.getElementById('mainTitle').innerText = "JOUEUR 1 : SÉLECTION";
                } else if (ROLE === 2) {
                    // JE SUIS JOUEUR 2 : Je cache la zone J1
                    document.getElementById('p1-zone').classList.add('hidden');
                    document.getElementById('p2-zone').classList.add('single-screen-mode');
                    document.getElementById('mainTitle').innerText = "JOUEUR 2 : SÉLECTION";
                }
            } else {
                // MODE LOCAL : On affiche "VS" dans le titre
                document.getElementById('mainTitle').innerText = "JOUEUR 1  VS  JOUEUR 2";
            }

            // 2. CHARGEMENT DES DONNÉES
            fetch('data.json')
                .then(res => res.json())
                .then(data => {
                    gameData = data;
                    
                    // On initialise seulement ce qu'on doit voir
                    if (MODE === 'local') {
                        initPhase(1, 'ships');
                        initPhase(2, 'ships');
                    } else {
                        // En online, on n'init que notre propre grille
                        initPhase(ROLE, 'ships');
                    }
                });
        });

        // --- FONCTIONS DU JEU (Grille, Selection...) ---
        // (Le code ici est identique à la version précédente, je remets l'essentiel)
        
        function initPhase(playerId, type) {
            const grid = document.getElementById(`p${playerId}-grid`);
            const instr = document.getElementById(`p${playerId}-instruction`);
            const btn = document.getElementById(`p${playerId}-btn`);
            
            grid.innerHTML = ''; btn.classList.remove('ready');
            players[playerId].selected = []; players[playerId].phase = type;
            const limit = CONFIG[type].limit;
            instr.innerHTML = `CHOISIS <span style="color:#00d2ff">${limit}</span> ${CONFIG[type].name.toUpperCase()}`;

            gameData[CONFIG[type].key].forEach(item => {
                const card = document.createElement('div');
                card.className = 'card';
                card.dataset.id = item.id;
                card.onclick = () => toggleSelection(playerId, item.id, limit, card);
                card.innerHTML = `<img src="${item.img}" onerror="this.src='https://placehold.co/100x100/333/red'"><span>${item.nom}</span>`;
                grid.appendChild(card);
            });
        }

        function toggleSelection(pid, id, limit, card) {
            const p = players[pid];
            const idx = p.selected.indexOf(id);
            if (idx > -1) { p.selected.splice(idx, 1); card.classList.remove('selected'); }
            else if (p.selected.length < limit) { p.selected.push(id); card.classList.add('selected'); }
            
            const btn = document.getElementById(`p${pid}-btn`);
            if (p.selected.length === limit) btn.classList.add('ready');
            else btn.classList.remove('ready');
        }

        function validatePhase(pid) {
            const p = players[pid];
            const btn = document.getElementById(`p${pid}-btn`);
            if (!btn.classList.contains('ready')) return;

            if (p.phase === 'ships') {
                initPhase(pid, 'drones'); // On recharge la grille avec les drones
            } else {
                // FIN DE SÉLECTION
                p.ready = true;
                document.getElementById(`p${pid}-zone`).innerHTML = `
                    <div style="margin-top:20%; text-align:center">
                        <h2 style="color:#00ff00; font-size:3rem">PRÊT !</h2>
                        <p>En attente de l'adversaire...</p>
                    </div>
                `;
                
                checkGameStart();
            }
        }

        function checkGameStart() {
            if (MODE === 'local') {
                if (players[1].ready && players[2].ready) {
                    setTimeout(() => { alert("LANCEMENT DU COMBAT !"); }, 1000);
                }
            } else {
                // MODE ONLINE
                // Note technique : Ici, sans serveur (PHP/NodeJS + Base de données),
                // le Joueur 1 ne peut pas savoir "vraiment" si le Joueur 2 a fini.
                // Pour ce prototype, on affiche juste le message d'attente.
                console.log("Mode Online : Attente signal serveur (Non implémenté dans cette démo HTML/JS)");
            }
        }
    </script>
</body>
</html>