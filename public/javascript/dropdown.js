//-------------------- Gestion Menus Déroulants --------------------
document.addEventListener('DOMContentLoaded', function () {
    function toggleDropdown(event) {
        event.stopPropagation();
        var dropdownButton = event.target.closest('.dropdown-button');
        if (dropdownButton) {
            var dropdowns = document.querySelectorAll(".dropdown-content");
            dropdowns.forEach(function (dropdown) {
                if (dropdown !== dropdownButton.nextElementSibling && $(dropdown).is(':visible')) {
                    $(dropdown).slideUp("slow");
                }
            });

            $(dropdownButton.nextElementSibling).slideToggle("slow");
        }
    }

    document.querySelectorAll('.dropdown-content a').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    $('.dropdown-button').off('click').each(function () {
        $(this).on('click', toggleDropdown);
    });
});
