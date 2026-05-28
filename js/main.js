document.addEventListener('DOMContentLoaded', () => {
    // 1. SELECTORES
    const filterButtons = document.querySelectorAll('.btn-filter');
    const destinationLinks = document.querySelectorAll('.filter-destino');
    const packageCards = document.querySelectorAll('.package-card');
    const dropdown = document.querySelector('.dropdown');

    // 2. FUNCIÓN MAESTRA DE FILTRADO
    function filtrarTarjetas(valorFiltro) {
        packageCards.forEach(card => {
            const cardCategory = card.getAttribute('data-categoria');
            
            // Si el filtro es 'todos' o coincide con la categoría/destino, se muestra
            if (valorFiltro === 'todos' || cardCategory === valorFiltro) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // 3. EVENTOS: Botones de categorías (Playa, Naturaleza, etc.)
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            filtrarTarjetas(button.getAttribute('data-filter'));
        });
    });

    // 4. EVENTOS: Menú desplegable de destinos
    destinationLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            filtrarTarjetas(link.getAttribute('data-target'));
        });
    });

    // 5. NOTA: Ya no necesitamos JS para abrir el menú 
    // porque usamos el :hover del CSS. 
    // Esto evita que el menú se "atasque".
});


