// vérifié par 
document.addEventListener("DOMContentLoaded", function () {
    let collisionPositions = {}; // Stocke les positions des collisions pour chaque camion
    let scoreFinalEnvoye = {}; // Empêche l'envoi multiple du score final par IP
    //let lastProgression = {}; // Stocke la dernière position du camion pour un déplacement fluide

    function getHeureDepart() {
    return fetch('./get_heure_depart.php')
        .then(response => response.json())
        .then(data => {
            if (data.heure_depart && data.duree) {
                return {
                    heureDepart: new Date(data.heure_depart),
                    duree: data.duree
                };
            } else {
                console.error("Erreur lors de la récupération de l'heure de départ :", data.error);
                return null;
            }
        })
        .catch(error => {
            console.error("Erreur réseau lors de la récupération de l'heure de départ :", error);
            return null;
        });
    }

    function updateCamionPosition(couleur) {
        fetch(`./get_InfoBOM.php?couleur=${couleur}`)
            .then(response => {
                    if (!response.ok) throw new Error(`Status HTTP : ${response.status}`);
                    return response.json();
                })
            .then(data => {
                if (data.error) {
                    console.error(`Erreur : ${data.error}`);
                    return;
                }

                data.forEach((camionData, index) => {
                    let ip = camionData.ip;
                    let totalPAVs = nbrPAV; // Nombre total de tours (PAV) sur la route
                    let pavsPasses = Math.round(camionData.progression / (100 / (totalPAVs))); // Nombre de PAV atteints (dépassé)
                    let progressionPourcentage = Math.min(100, camionData.progression); //Progress de la BOM (%)
                    let nbrCollision = camionData.nbrCollision ?? 0; //Nbr Collision par BOM
                    let collisions = camionData.collisions; //Coordonnées des collisions
                    let camion = document.querySelector(`.imageCamion[data-couleur="${couleur}"][data-ip="${ip}"]`);
                    
                    if (!camion) return; // Si le camion n'existe pas, on ignore

                    let distance = Math.abs(progressionPourcentage - camion.dataset.prevProgression || 0);

                    // Définir la durée en fonction de la distance
                    let animationDuration;
                    if (distance <= 10) {
                        animationDuration = 2000 + (distance * 100); // Entre 2 et 3 secondes pour les petites distances
                    } else if (distance >= 50) {
                        animationDuration = 500 + (distance * 10); // Rapide si distance grande
                    } else {
                        animationDuration = 1500 + (distance * 20); // Progression moyenne
                    }

                    // Appliquer la transition fluide
                    camion.style.transition = `left ${animationDuration / 1000}s linear`;
                    camion.style.left = `${progressionPourcentage}%`;

                    // Sauvegarder la dernière progression
                    camion.dataset.prevProgression = progressionPourcentage;

                    // Mise à jour du score
                    let scoreText = document.getElementById(`score-${ip}`);
                    
                    
                    //console.log(`progression = ${progressionPourcentage}, pavsPasses = ${pavsPasses}`);
                    let maxPossibleScore = 0; // Nouveau score max dynamique
                    let scoreBrut = 0;

                    if (optionMode === 0) {
                        // OPTION A : Score fixe par PAV
                        scoreBrut = pavsPasses * ptsRecolte;
                        maxPossibleScore = totalPAVs * ptsRecolte; // Score max basé sur le nombre de PAV et ptsRecolte
                    } else if (optionMode === 1) {
                        // OPTION B : Score progressif
                        let basePts = ptsRecolte * 0.6; // La première PAV rapporte 60% du score de base
                        let increment = (ptsRecolte * 1.4 - basePts) / (totalPAVs - 1); // Augmente progressivement

                        for (let i = 0; i < pavsPasses; i++) {
                            scoreBrut += basePts + (i * increment); // Ajoute des points de manière progressive
                        } // for i

                        // Calcul du score max théorique en Option B
                        for (let i = 0; i < totalPAVs; i++) {
                            maxPossibleScore += basePts + (i * increment);
                        } // for i
                    } // if optionmode

                    // Appliquer le malus des collisions
                    scoreBrut -= nbrCollision * malusCollision;
                    scoreBrut = Math.max(0, scoreBrut); // Empêcher un score négatif

                    // Affichage sans normalisation, mais avec une limite dynamique
                    scoreBrut = Math.min(maxPossibleScore, Math.max(0, scoreBrut));

                    scoreText.textContent = `${Math.round(scoreBrut)}/${Math.round(maxPossibleScore)}`;
                    // Envoie le score au serveur via AJAX
                    const tolerance = 2.5;
                    if (Math.abs(progressionPourcentage - 100) > tolerance) {
                        // Remet à zéro uniquement si c'est déjà vrai (pour éviter écrasements inutiles)
                        if (scoreFinalEnvoye[ip]) {
                            //console.log(`Remise à zéro du flag scoreFinalEnvoye pour l'IP ${ip} car progression est à ${progressionPourcentage}`);
                            scoreFinalEnvoye[ip] = false;
                        } // if scorefinalEnv
                    } // if math
                    if (Math.abs(progressionPourcentage - 100) <= tolerance && !scoreFinalEnvoye[ip]) {
                        scoreFinalEnvoye[ip] = true; // Marquer que le score final a été envoyé
                        getHeureDepart().then(result => {
                            if (result) {
                                const maintenant = new Date();
                                const tempsEcoule = (maintenant - result.heureDepart) / 1000; // en secondes
                                const tempsTotalPartie = result.duree; // Durée totale de la partie en secondes
                                const tempsRestant = Math.max(0, tempsTotalPartie - tempsEcoule);
                                const scoreFinal = scoreBrut + tempsRestant;

                                console.log("scoreBrut =", scoreBrut, "tempsRestant =", tempsRestant);
                                console.log("Données envoyées :", { ip, score: Math.round(scoreFinal) });

                                fetch("./update_score.php", {
                                    method: "POST",
                                    headers: { "Content-Type": "application/json" },
                                    body: JSON.stringify({ ip, score: Math.round(scoreFinal) })
                                })
                                .then(res => res.json())
                                .then(json => {
                                    if (!json.success) console.error("Erreur enregistrement score :", json.error);
                                })
                                .catch(err => console.error("Erreur réseau lors de l’envoi du score :", err));
                            }
                        });
                        
                    } else if (!scoreFinalEnvoye[ip]) {
                        fetch("./update_score.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ ip, score: Math.round(scoreBrut) })
                        })
                        .then(res => res.text()) // on lit la réponse brute en texte d'abord
                        .then(text => {
                            //console.log("Réponse brute du serveur :", text);
                            try {
                                const json = JSON.parse(text);
                                if (!json.success) console.error("Erreur enregistrement score :", json.error);
                            } catch(e) {
                                console.error("Réponse JSON invalide :", e);
                            }
                        })
                        .catch(err => console.error("Erreur réseau lors de l’envoi du score :", err));
                    } else {
                        console.log(`Score final déjà envoyé pour l'IP ${ip}, pas de mise à jour supplémentaire.`);
                    }

                    // Ajout ou mise à jour du pseudo au-dessus du camion
                    let pseudoSpan = camion.parentElement.querySelector(`.pseudo[data-ip="${ip}"]`);
                    
                    if (!pseudoSpan) {
                        pseudoSpan = document.createElement("span");
                        pseudoSpan.className = "pseudo";
                        pseudoSpan.dataset.ip = ip;
                        pseudoSpan.style.position = "absolute";
                        pseudoSpan.style.fontWeight = "bold";
                        pseudoSpan.style.background = "rgba(255, 255, 255, 0.8)";
                        pseudoSpan.style.padding = "3px 5px";
                        pseudoSpan.style.borderRadius = "5px";
                        pseudoSpan.style.whiteSpace = "nowrap";
                        pseudoSpan.style.display = "none"; // Caché par défaut
                        pseudoSpan.style.transition = "left 0.5s linear"; // Ajout de transition
                        camion.parentElement.appendChild(pseudoSpan);
                    }

                    // Met à jour le pseudo du joueur
                    let joueurPseudo = camionData.joueur ?? ""; // Récupère le pseudo du joueur

                    if (joueurPseudo !== "") {
                        pseudoSpan.textContent = joueurPseudo;
                        pseudoSpan.style.display = "block"; // Affiche le pseudo si un joueur est défini
                    } else {
                        pseudoSpan.style.display = "none"; // Cache le pseudo s'il n'y a pas de joueur
                    }

                    // Appliquer la transition
                    pseudoSpan.style.transition = `left ${animationDuration / 1000}s linear`;

                    // Positionner le pseudo au-dessus du camion avec transition fluide
                    pseudoSpan.style.left = `${progressionPourcentage}%`;
                    pseudoSpan.style.top = `${camion.offsetTop - 20}px`; // Décalage au-dessus du camion


                    // Grisage des TOM en fonction de la progression du camion
                    let pavs = document.querySelectorAll(`.imagePAV[data-couleur="${couleur}"][data-ip="${ip}"]`);
                    console.log(`PAVs trouvés pour ${ip} :`, pavs.length);
                    //console.log(`Progression du camion (${ip}) :`, progressionPourcentage);
                    pavs.forEach((pav, i) => {
                        let espacement = (100 / (pavs.length + 1)).toFixed(1);
                        espacement = parseFloat(espacement);
                        let pavPosition = (i + 1) * espacement;

                        console.log(`PAV ${i + 1}: position=${pavPosition.toFixed(2)}, progression=${progressionPourcentage.toFixed(2)}`);

                        if (progressionPourcentage >= pavPosition) {
                            pav.style.opacity = "0.3";
                            pav.style.filter = "grayscale(100%)";
                        } else {
                            pav.style.opacity = "1";
                            pav.style.filter = "none";
                        } // else
                    });

                    // ========= Gestion des collisions =========
                    // Suppression des collisions si leur nombre diminue
                    if (!collisionPositions[ip]) {
                        collisionPositions[ip] = [];
                    }

                    let existingIndexes = Object.keys(collisionPositions[ip]); // Index existants en mémoire
                    let collisionIndexes = collisions.map(c => c.index); // Index en base

                    // Supprimer les collisions qui ne sont plus en base
                    existingIndexes.forEach(index => {
                        if (!collisionIndexes.includes(parseInt(index))) {
                            let el = document.querySelector(`.collision[data-ip="${ip}"][data-index="${index}"]`);
                            if (el) el.remove();
                            delete collisionPositions[ip][index]; // Supprime aussi du cache JS
                        }
                    });

                    // Ajouter les collisions existantes en base si elles ne sont pas encore affichées
                    collisions.forEach(collision => {
                        let index = collision.index;
                        let x = collision.x;
                        let y = collision.y;

                        if (!collisionPositions[ip][index]) {
                            let imgCollision = document.createElement("img");
                            imgCollision.src = "./img/Crash.png";
                            imgCollision.className = "collision";
                            imgCollision.dataset.ip = ip;
                            imgCollision.dataset.index = index;
                            imgCollision.dataset.x = x;
                            imgCollision.dataset.y = y;
                            imgCollision.style = `
                                position: absolute;
                                top: ${y}%;
                                left: ${x}%;
                                transform: translate(-50%, -50%);
                                width: 40px;
                                opacity: 0.7;
                                z-index: 5;
                            `;
                            camion.parentElement.appendChild(imgCollision);
                            collisionPositions[ip][index] = { x, y };
                        }
                    });

                    // Ajouter de nouvelles collisions si le nombre a augmenté
                    if (collisionIndexes.length < nbrCollision) {
                        let newIndex = collisionIndexes.length; // Nouvel index
                        let collisionX = progressionPourcentage + (Math.random() * 10 - 5);
                        let collisionY = 50 + (Math.random() * 10 - 5);

                        let newCollision = { x: collisionX, y: collisionY, index: newIndex };

                        collisionPositions[ip][newIndex] = newCollision;

                        let imgCollision = document.createElement("img");
                        imgCollision.src = "./img/Crash.png";
                        imgCollision.className = "collision";
                        imgCollision.dataset.ip = ip;
                        imgCollision.dataset.index = newIndex;
                        imgCollision.dataset.x = collisionX;
                        imgCollision.dataset.y = collisionY;
                        imgCollision.style = `
                            position: absolute;
                            top: ${collisionY}%;
                            left: ${collisionX}%;
                            transform: translate(-50%, -50%);
                            width: 40px;
                            opacity: 0.7;
                            z-index: 5;
                        `;
                        camion.parentElement.appendChild(imgCollision);

                        // Enregistrer les nouvelles collisions dans la base
                        fetch('update_collisions.php', {
                            method: 'POST',
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ ip, collisions: Object.values(collisionPositions[ip]) })
                        });
                    }
                });
            })
        .catch(error => console.error("Erreur AJAX :", error));
    }

    function updateAllCamions() {
        let couleurs = ["Jaune", "Vert", "Bleu", "Noir"];
        couleurs.forEach(couleur => updateCamionPosition(couleur));
    }

    setInterval(updateAllCamions, 1000);
});
