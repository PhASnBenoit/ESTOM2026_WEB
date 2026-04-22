let timerInterval;
let remainingTime = timerDuration; // Valeur initiale du temps restant
let isTimerRunning = false; // État du timer
let isStarting = false;

function startCountdown(duration) {
    const timerDiv = document.getElementById('timer');
    remainingTime = duration; // Mise à jour de la valeur de remainingTime

    function updateTimer() {
        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        timerDiv.textContent = `Temps restant : ${minutes}m ${seconds}s`;

        if (remainingTime > 0) {
            remainingTime--; // Met à jour la variable restante
        } else {
            timerDiv.textContent = "Temps écoulé !";
            clearInterval(timerInterval);
            isTimerRunning = false;
            updateDatabaseStatus('stopped');
            updateButton(3); // "Lancer le timer"

            // Gestion de l'affichage du Podium
            showPodium();
            //updateDatabaseStatus('podium'); //optionnel
        }
    }

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
}

function updateButton(state) {
    const startButton = document.getElementById('startGameButton');
    const resetButton = document.getElementById('resetGameButton');

    switch (state) {
        case 1: // Timer en cours
            startButton.textContent = "Mettre en pause";
            resetButton.style.display = 'inline';
            break;
        case 2: // Timer en pause
            startButton.textContent = "Reprendre la partie";
            resetButton.style.display = 'inline';
            break;
        case 3: // Timer terminé ou non démarré
            startButton.textContent = "Lancer une nouvelle partie";
            resetButton.style.display = 'none';
            break;
    }
}

// Fonction AJAX pour récupérer l'état actuel
window.addEventListener('DOMContentLoaded', () => {
    fetch('./get_timer_state.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            const status = data.status;
            remainingTime = parseInt(data.temps_restant) || timerDuration;

            switch (status) {
                case 'running':
                    isTimerRunning = true;
                    startCountdown(remainingTime); // Appel avec la valeur correcte
                    updateButton(1);
                    break;
                case 'paused':
                    isTimerRunning = false;
                    const minutes = Math.floor(remainingTime / 60);
                    const seconds = remainingTime % 60;
                    document.getElementById('timer').textContent = `Temps restant : ${minutes}m ${seconds}s`;
                    updateButton(2);
                    break;
                case 'loading':
                    document.getElementById('timer').textContent = `Chargement de la partie...`;
                    updateButton(1);
                    break;
                case 'stopped':
                default:
                    isTimerRunning = false;
                    document.getElementById('timer').textContent = "Cliquez sur le bouton pour lancer la Partie !";
                    updateButton(3);
                    break;
            }
        })
        .catch(error => {
            console.error("Erreur lors de la récupération du timer :", error);
        });
});

function updateDatabaseStatus(status, tempsRestant = null) {
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "./update_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    let params = `status=${status}`;
    if (tempsRestant !== null) {
        params += `&temps_restant=${tempsRestant}`;
    }

    xhr.onload = function () {
        if (xhr.status === 200) {
            console.log(`Statut mis à jour : ${status}`);
        } else {
            console.error("Erreur lors de la mise à jour du statut.");
        }
    };

    xhr.send(params);
}

document.getElementById('startGameButton').addEventListener('click', function () {
    if (isStarting) return;

    if (isTimerRunning) {
        clearInterval(timerInterval); // Met en pause le timer
        isTimerRunning = false;
        updateDatabaseStatus('paused');
        updateButton(2); // "Reprendre la partie"
    } else if (remainingTime === timerDuration || remainingTime === 0) {
        isStarting = true;
        remainingTime = timerDuration; // Réinitialiser à la durée initiale
        updateDatabaseStatus('stopped');
        // Compte à rebours de 5 secondes avant le démarrage
        let countdown = 5;
        const timerDiv = document.getElementById('timer');
        timerDiv.textContent = `Démarrage dans : ${countdown}s`;
        const countdownInterval = setInterval(() => {
            countdown--;
            timerDiv.textContent = `Démarrage dans : ${countdown}s`;
            updateDatabaseStatus('loading'); // Chargement et mise en place du jeu (5s)
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                isStarting = false;
                updateDatabaseStatus('running'); // Mise à jour de la base de données
                startCountdown(remainingTime); // Appel avec la bonne valeur
                isTimerRunning = true;
                updateButton(1);
            }
        }, 1000);
    } else {
        // === CAS DE LA REPRISE D'UNE PARTIE EN PAUSE ===
        startCountdown(remainingTime); // Reprendre à partir du temps restant
        isTimerRunning = true;
        updateDatabaseStatus('running', remainingTime); // On passe aussi le temps restant
        updateButton(1); // "Mettre en pause"
    }
});

document.getElementById('resetGameButton').addEventListener('click', function () {
    clearInterval(timerInterval); // Annuler le timer en cours
    remainingTime = timerDuration; // Réinitialiser le temps
    document.getElementById('timer').textContent = "Cliquez sur le bouton pour lancer la partie";
    isTimerRunning = false;
    updateDatabaseStatus('stopped');
    updateButton(3); // "Lancer le timer"
});
