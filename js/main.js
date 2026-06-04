document.addEventListener('DOMContentLoaded', () => {
    const filterButtons = document.querySelectorAll('.btn-filter');
    const destinationLinks = document.querySelectorAll('.filter-destino');
    const packageCards = document.querySelectorAll('.package-card');
    const dropdown = document.querySelector('.dropdown');
    const searchInput = document.querySelector('.search-section__form input[type="search"]');
    const selectDuracion = document.getElementById('select-duracion');

    function coincideDuracion(card, valorDuracion) {
        if (!valorDuracion) return true;
        const duracion = (card.getAttribute('data-duracion') || '').toLowerCase();
        if (valorDuracion === 'completo') {
            return duracion.includes('completo') || duracion.includes('full') || duracion.includes('8') || duracion.includes('10') || duracion.includes('12');
        }
        if (valorDuracion === 'medio') {
            return duracion.includes('medio') || duracion.includes('4') || duracion.includes('5') || duracion.includes('3');
        }
        return true;
    }

    function filtrarTarjetas(valorFiltro, textoBusqueda = '', valorDuracion = '') {
        let visibles = 0;
        packageCards.forEach(card => {
            const categoria = card.getAttribute('data-categoria') || '';
            const titulo = card.querySelector('h4')?.textContent.toLowerCase() || '';
            const descripcion = card.querySelector('.package-card__description')?.textContent.toLowerCase() || '';
            const ubicacion = card.querySelector('.tour-meta')?.textContent.toLowerCase() || '';

            const coincideCategoria = valorFiltro === 'todos' || (categoria.toLowerCase().split(' ').some(cat => cat === valorFiltro));
            const coincideBusqueda = textoBusqueda === '' ||
                titulo.includes(textoBusqueda) ||
                descripcion.includes(textoBusqueda) ||
                ubicacion.includes(textoBusqueda);

            const mostrar = coincideCategoria && coincideBusqueda && coincideDuracion(card, valorDuracion);
            card.style.display = mostrar ? '' : 'none';
            if (mostrar) visibles++;
        });

        const noRes = document.getElementById('no-resultados');
        if (noRes) noRes.style.display = visibles === 0 ? 'block' : 'none';
    }

    function getValores() {
        return {
            filtro: filtroActivo,
            busqueda: searchInput ? searchInput.value.trim().toLowerCase() : '',
            duracion: selectDuracion ? selectDuracion.value : ''
        };
    }

    let filtroActivo = 'todos';

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('is-active'));
            button.classList.add('is-active');
            filtroActivo = button.getAttribute('data-filter');
            const v = getValores();
            filtrarTarjetas(v.filtro, v.busqueda, v.duracion);
        });
    });

    destinationLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            filtroActivo = link.getAttribute('data-target');
            const v = getValores();
            filtrarTarjetas(v.filtro, v.busqueda, v.duracion);
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const v = getValores();
            filtrarTarjetas(v.filtro, v.busqueda, v.duracion);
        });

        searchInput.closest('form')?.addEventListener('submit', e => e.preventDefault());
    }

    if (selectDuracion) {
        selectDuracion.addEventListener('change', () => {
            const v = getValores();
            filtrarTarjetas(v.filtro, v.busqueda, v.duracion);
        });
    }
});
