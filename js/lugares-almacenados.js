document.addEventListener('DOMContentLoaded', () => {
    // 1. Base de datos interna de los lugares
    const destinos = {
        "manglares": {
            titulo: "Santuario Nacional Los Manglares",
            ubicacion: "Zarumilla / Puerto Pizarro",
            precio: 70.00,
            imagen: "../assets/manglares.jpg",
            descripcion: "Un ecosistema magnífico de bosques flotantes donde el agua dulce de los ríos se mezcla con el océano. Hogar de las famosas conchas negras.",
            incluye: ["Paseo en bote guiado", "Chaleco salvavidas", "Visita al estero principal"]
        },
        "isla-pajaros": {
            titulo: "Isla de los Pájaros",
            ubicacion: "Puerto Pizarro, Tumbes",
            precio: 45.00,
            imagen: "../assets/isla de los pajaros.jpg",
            descripcion: "El refugio perfecto de miles de aves locales y migratorias. Al atardecer, ver el regreso de las especies a los manglares es un espectáculo mágico.",
            incluye: ["Transporte marítimo ida y vuelta", "Guía especializado en avistamiento", "Prismáticos en préstamo"]
        }
        // Puedes agregar aquí los 15 o 20 destinos siguiendo la misma estructura
    };

    // 2. Leer los parámetros de la URL (?sitio=nombre)
    const urlParams = new URLSearchParams(window.location.search);
    const sitioSeleccionado = urlParams.get('sitio');

    // Verificación de seguridad si el sitio no existe en el objeto
    if (!sitioSeleccionado || !destinos[sitioSeleccionado]) {
        document.body.innerHTML = "<h2>Destino no encontrado</h2><a href='index.html'>Volver al inicio</a>";
        return;
    }

    const datos = destinos[sitioSeleccionado];

    // 3. Inyectar la información en el HTML de forma dinámica
    document.getElementById('dinamico-titulo').textContent = datos.titulo;
    document.getElementById('dinamico-ubicacion').textContent = datos.ubicacion;
    document.getElementById('dinamico-precio').textContent = `S/ ${datos.precio.toFixed(2)}`;
    document.getElementById('dinamico-descripcion').textContent = datos.descripcion;
    
    const imgElement = document.getElementById('dinamico-img');
    imgElement.src = datos.imagen;
    imgElement.alt = datos.titulo;

    // Renderizar la lista de lo que incluye
    const listaIncluye = document.getElementById('dinamico-incluye');
    listaIncluye.innerHTML = "";
    datos.incluye.forEach(item => {
        listaIncluye.innerHTML += `<li><i class="fa-solid fa-check" style="color: green; margin-right: 8px;"></i> ${item}</li>`;
    });

    // 4. Lógica interactiva de cálculo de precios en la sección de reserva
    const inputPersonas = document.getElementById('cantidad-personas');
    const spanPrecioFinal = document.getElementById('precio-final');

    function calcularTotal() {
        const cantidad = parseInt(inputPersonas.value) || 1;
        const total = cantidad * datos.precio;
        spanPrecioFinal.textContent = `S/ ${total.toFixed(2)}`;
    }

    inputPersonas.addEventListener('input', calcularTotal);
    calcularTotal(); // Inicializar precio total con 1 persona

    // Enviar formulario de reserva
    document.getElementById('booking-form').addEventListener('submit', (e) => {
        e.preventDefault();
        const fecha = document.getElementById('fecha-reserva').value;
        alert(`¡Reserva Solicitada con éxito!\nDestino: ${datos.titulo}\nFecha: ${fecha}\nPersonas: ${inputPersonas.value}\nTotal: ${spanPrecioFinal.textContent}`);
    });
});