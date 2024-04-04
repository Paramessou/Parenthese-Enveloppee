//-------------------- Gestion Menus Déroulants --------------------
// let clickCount = 0;
document.addEventListener('DOMContentLoaded', function () { // DOMContentLoaded pour s'assurer que le DOM est chargé
    // Menu déroulant pour pages utilisateurs et page connexion
    function toggleDropdown(event) { // Fonction pour ouvrir et fermer les menus déroulants
        // clickCount++; // Incrémente le compteur à chaque appel de la fonction
        // console.log('toggleDropdown appelé ' + clickCount + ' fois'); // Affiche le nombre d'appels de la fonction
        // console.log(event.target);
        event.stopPropagation();
        // console.log('Clic détecté');

        var dropdownButton = event.target.closest('.dropdown-button');
        if (dropdownButton) {
            // console.log('Bouton de menu déroulant trouvé', dropdownButton);

            var dropdowns = document.querySelectorAll(".dropdown-content");
            dropdowns.forEach(function (dropdown) {
                if (dropdown !== dropdownButton.nextElementSibling && $(dropdown).is(':visible')) {
                    // console.log('Fermeture d\'un menu déroulant', dropdown);
                    $(dropdown).slideUp("slow");
                }
            });

            // console.log('Ouverture ou fermeture du menu déroulant', dropdownButton.nextElementSibling);
            $(dropdownButton.nextElementSibling).slideToggle("slow");
        } //else {
        //     console.log('Aucun bouton de menu déroulant trouvé');
        // }
    }

    // Gérez le clic sur les liens à l'intérieur des menus pour éviter la fermeture immédiate
    document.querySelectorAll('.dropdown-content a').forEach(function (link) { // Itère sur chaque lien dans les menus déroulants
        link.addEventListener('click', function (event) { // Ajoute un écouteur d'événements pour le clic
            event.stopPropagation(); // Empêche la propagation de l'événement de clic pour éviter la fermeture immédiate du menu
        });
    });

    // document.querySelectorAll('.dropdown-button').forEach(function (button) { // Itère sur chaque bouton de menu déroulant
    //     button.addEventListener('click', toggleDropdown); // Ajoute un écouteur d'événements pour le clic
    // });
    $('.dropdown-button').off('click').each(function () { // Itère sur chaque bouton de menu déroulant, empêche les doublons d'écouteurs d'événements et réinitialise les écouteurs d'événements
        $(this).on('click', toggleDropdown); // Ajoute un écouteur d'événements pour le clic sur chaque bouton de menu déroulant
    });
});