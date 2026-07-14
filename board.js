// board.js - Version finale avec support prévisualisation

const GRID_SIZE = 10;

function renderBoard(etat, currentPlayerId, selector, lastAction) {
    const $container = $(selector);
    if ($container.length === 0) return;

    $container.empty();

    const joueur1 = etat.joueur1;
    const joueur2 = etat.joueur2;
    const v1Pos = joueur1.vaisseau.position;
    const v2Pos = joueur2.vaisseau.position;

    // Gestion de l'effet d'attaque
    let attackCell = null;
    if (lastAction && lastAction.succes && typeof lastAction.message === 'string') {
        if (lastAction.message.indexOf('tire') !== -1 || lastAction.message.indexOf('drone') !== -1) {
            const advId = currentPlayerId === 1 ? 2 : 1;
            const advData = etat['joueur' + advId];
            if (advData && advData.vaisseau && advData.vaisseau.position) {
                attackCell = { x: advData.vaisseau.position.x, y: advData.vaisseau.position.y };
            }
        }
    }

    const $table = $('<table class="board-grid"></table>');

    for (let y = 1; y <= GRID_SIZE; y++) {
        const $tr = $('<tr></tr>');
        for (let x = 1; x <= GRID_SIZE; x++) {
            
            // --- C'EST ICI LA CORRECTION CRUCIALE ---
            // On ajoute .attr('data-x', x).attr('data-y', y) pour que le CSS puisse trouver la case
            const $td = $('<td></td>').attr('data-x', x).attr('data-y', y);
            
            // Interaction : Clic sur la grille remplit les inputs
            $td.on('click', function() {
                $('#move_x').val(x);
                $('#move_y').val(y).trigger('input'); // Force la mise à jour visuelle
            });

            let content = '';
            let classes = ['cell'];

            // Vaisseau J1
            if (v1Pos.x === x && v1Pos.y === y) {
                content += 'V1';
                classes.push('cell-v1');
                if (joueur1.vaisseau.energie <= 0) classes.push('cell-destroyed');
            }

            // Vaisseau J2
            if (v2Pos.x === x && v2Pos.y === y) {
                content += (content ? ' / ' : '') + 'V2';
                classes.push('cell-v2');
                if (joueur2.vaisseau.energie <= 0) classes.push('cell-destroyed');
            }

            // Drones
            joueur1.drones.forEach(d => {
                if (d.position.x === x && d.position.y === y) {
                    content += (content ? '+' : '') + 'D1';
                    classes.push('cell-d1');
                }
            });
            joueur2.drones.forEach(d => {
                if (d.position.x === x && d.position.y === y) {
                    content += (content ? '+' : '') + 'D2';
                    classes.push('cell-d2');
                }
            });

            if (attackCell && attackCell.x === x && attackCell.y === y) {
                classes.push('cell-attack');
            }

            if (!content) content = '&nbsp;';

            $td.html(content);
            $td.addClass(classes.join(' '));
            $tr.append($td);
        }
        $table.append($tr);
    }

    $container.append($table);
}