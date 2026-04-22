function loadPodium() {
    fetch('./get_podium.php')
        .then(response => response.json())
        .then(data => {
            console.log("Réponse JSON reçue :", data);
            const container = document.getElementById('classementContent');
            container.innerHTML = '';

            data.forEach((joueur, index) => {
                const div = document.createElement('div');
                div.className = `podium-place podium-${index}`;
                div.innerHTML = `
                    <span style="font-weight:bold;">${joueur.joueur}</span><br>
                    <img src="./img/BOM-${joueur.couleur}.png" alt="BOM ${joueur.couleur}" style="width:90px;"><br>
                    <span style="text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000, 0 0 5px #000;">
                        Score : ${joueur.score}
                    </span>
                `;
                container.appendChild(div);
            });
        })
        .catch(error => {
            console.error("Erreur lors du chargement du podium :", error);
        });
}
