<?php
// Importamos el Header modular (incluye Tailwind, sesión y apertura de etiquetas)
require_once 'header.php'; 
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-20">

    <div class="text-center space-y-4">
        <span class="text-xs font-semibold tracking-widest text-amber-500 uppercase">Nuestra Historia</span>
        <h1 class="text-4xl md:text-5xl font-serif font-bold text-slate-100 tracking-tight">Detrás de Sartorial</h1>
        <div class="h-1 w-12 bg-amber-500 mx-auto mt-4"></div>
    </div>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-slate-900 border border-slate-800 shadow-xl">
            <img src="https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&q=80&w=600" 
                 alt="Taller de sastrería artesanal" 
                 class="w-full h-full object-cover opacity-90 hover:scale-105 transition duration-500">
        </div>
        <div class="space-y-6">
            <h2 class="text-2xl md:text-3xl font-serif font-bold text-slate-100">El Arte de la Confección</h2>
            <p class="text-slate-400 leading-relaxed">
                Sartorial nació con un propósito claro: rescatar el valor de los pequeños detalles en el vestir diario. En un mundo donde la moda rápida domina, decidimos volver a las raíces, enfocándonos exclusivamente en el accesorio que define el carácter de un traje: la corbata.
            </p>
            <p class="text-slate-400 leading-relaxed">
                Cada pieza de nuestra colección pasa por las manos de expertos artesanos que cortan, cosen y estructuran cada patrón individualmente utilizando las técnicas tradicionales de la alta costura.
            </p>
        </div>
    </section>

    <section class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div class="space-y-6 order-2 md:order-1">
            <h2 class="text-2xl md:text-3xl font-serif font-bold text-slate-100">Materiales de Origen Noble</h2>
            <p class="text-slate-400 leading-relaxed">
                No creemos en los atajos. Por eso, importamos directamente todos nuestros hilos de seda natural desde las tejedurías históricas del lago de Como, en Italia. 
            </p>
            <p class="text-slate-400 leading-relaxed">
                Tanto la lana virgen de origen ético como los algodones de fibras largas que usamos para los forros interiores garantizan que tu nudo conserve una caída, peso y estructura perfectas durante toda la jornada.
            </p>
        </div>
        <div class="aspect-[4/3] rounded-2xl overflow-hidden bg-slate-900 border border-slate-800 shadow-xl order-1 md:order-2">
            <img src="https://images.unsplash.com/photo-1598530022011-52a96720f4d9?auto=format&fit=crop&q=80&w=600" 
                 alt="Selección de seda de alta calidad" 
                 class="w-full h-full object-cover opacity-90 hover:scale-105 transition duration-500">
        </div>
    </section>

</div>

<?php 
// Importamos el Footer modular (cierra main, dibuja footer y cierra etiquetas HTML)
require_once 'footer.php'; 
?>