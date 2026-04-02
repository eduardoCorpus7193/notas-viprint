document.addEventListener("DOMContentLoaded", function () {
    const toggleButton = document.getElementById("toggleFiltersBtn");
    const filtersSection = document.getElementById("filtersSection");
    const filtersActive = document.body.dataset.filtersActive === "1";

    if (toggleButton && filtersSection) {
        if (filtersActive) {
            filtersSection.classList.remove("d-none");
            toggleButton.textContent = "Ocultar filtros avanzados";
        } else {
            filtersSection.classList.add("d-none");
            toggleButton.textContent = "Mostrar filtros avanzados";
        }

        toggleButton.addEventListener("click", function () {
            const isHidden = filtersSection.classList.contains("d-none");

            if (isHidden) {
                filtersSection.classList.remove("d-none");
                toggleButton.textContent = "Ocultar filtros avanzados";
            } else {
                filtersSection.classList.add("d-none");
                toggleButton.textContent = "Mostrar filtros avanzados";
            }
        });
    }

    const deleteButtons = document.querySelectorAll(".btn-delete-note");

    deleteButtons.forEach((button) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();

            const deleteUrl = this.getAttribute("href");

            Swal.fire({
                title: "¿Eliminar nota?",
                text: "Esta acción no se puede deshacer.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#d63384",
                cancelButtonColor: "#6c757d",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = deleteUrl;
                }
            });
        });
    });
});