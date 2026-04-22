// vérifié par PhA

// Objet pour stocker l'état précédent des connexions
let previousState = {};

function updateConnexion() {
    fetch('./get_connexionIP.php')
        .then(response => response.json())
        .then(data => {
            console.log("Données reçues :", data); // DEBUG

            let currentState = {}; // Stocke l'état actuel
            let pavUsedIPs = {}; // Suivi des IPs déjà attribuées pour éviter les doublons
            let pavCount = { "Jaune": 0, "Vert": 0, "Bleu": 0, "Noir": 0 };

            // Réinitialiser les IP affichées
            document.querySelectorAll('.ip-text').forEach(span => {
                span.textContent = 'N/A';
            });

            data.forEach(entry => {
                let couleur = entry.couleur;
                let ip = entry.ip;
                let type = entry.source; // "BOM" ou "PAV"
                let isConnected = entry.connected === 1;

                console.log(`Vérification ${type} - Couleur: ${couleur}, IP: ${ip}, Connecté: ${isConnected}`);

                if (type === "BOM") {
                    let bomElements = document.querySelectorAll(`[id^="BOM-${couleur}-"]`); // Récupère tous les BOM de cette couleur

                    bomElements.forEach((bomElement, index) => {
                        let ipElement = document.getElementById(`BOM-IP-${couleur}-${index}`);

                        currentState[`BOM-${couleur}-${index}`] = isConnected;

                        if (isConnected) {
                            // Vérifier si l'état était différent avant d'ajouter l'effet
                            if (!previousState[`BOM-${couleur}-${index}`]) {
                                bomElement.classList.add('connecte');
                                bomElement.classList.remove('grise', 'deconnecte');
                            }
                            if (ipElement && ipElement.textContent !== ip) {
                                ipElement.textContent = ip;
                            }
                        }
                    });
                } else if (type === "PAV" && pavCount[couleur] < 10) {
                    let pavElements = document.querySelectorAll(`[id^="PAV-${couleur}-"]`);

                    // Vérifier qu'on n'a pas déjà attribué cette IP
                    if (!pavUsedIPs[ip]) {
                        for (let i = 0; i < pavElements.length; i++) {
                            let pavElement = pavElements[i];
                            let ipElement = document.getElementById(`PAV-IP-${couleur}-${i}`);
                            let key = `PAV-${couleur}-${i}`;

                            // Vérifier si ce PAV n'est pas déjà pris
                            if (!currentState[key]) {
                                currentState[key] = isConnected;

                                if (currentState[key] == isConnected) {
									//console.log("PAV connected");
                                    if (!previousState[key]) {
                                        pavElement.classList.remove('grise', 'deconnecte');
                                        pavElement.classList.add('connecte');
                                    } // if previous
                                    if (ipElement && ipElement.textContent !== ip) {
                                        ipElement.textContent = ip;
                                    } // if ip

                                    // Marquer cette IP comme utilisée
                                    pavUsedIPs[ip] = true;
                                    break; // Sortir de la boucle pour ne pas attribuer la même IP à plusieurs TOM
                                } // if isConnected
                            } // if key
                        } // for i
                    } // if pav...
                } // else 
            });

            // Gestion des déconnexions
            Object.keys(previousState).forEach(id => {
                if (previousState[id] && !currentState[id]) {
                    let element = document.getElementById(id);
                    if (element) {
                        element.classList.add('deconnecte');
                        element.classList.remove('connecte');

                        // Après 5 secondes, le remet en gris
                        setTimeout(() => {
                            element.classList.add('grise');
                            element.classList.remove('deconnecte');
                        }, 2000);
                    }
                }
            });

            // Mettre à jour l'état précédent
            previousState = currentState;
        })
        .catch(error => console.error("Erreur lors de la récupération des connexions:", error));
}

// Rafraîchir les données toutes les 2.5 secondes
setInterval(updateConnexion, 2500);

// Lancer une mise à jour immédiate au chargement
updateConnexion();
