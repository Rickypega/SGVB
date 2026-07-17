<?php
$tituloPagina = 'Visor Digital de Lectura SGBV | ' . htmlspecialchars($recurso->titulo ?? 'Recurso');
require_once __DIR__ . '/../../layouts/header.php';

// Obtener datos y preparar texto de lectura formateado en párrafos atractivos
$textoCompleto = trim($recurso->descripcion ?? 'Este material digital está disponible en formato exclusivo para lectura en línea protegida SGBV. Disfruta de la experiencia inmersiva y de nuestro reproductor de voz alta Text-to-Speech.');
if (empty($textoCompleto)) {
    $textoCompleto = "Bienvenido a la lectura digital de " . htmlspecialchars($recurso->titulo) . " de " . htmlspecialchars($recurso->autor) . ".\n\nEste recurso se encuentra sincronizado con el catálogo literario SGBV bajo protección de derechos de autor digital. Te recordamos que la reproducción parcial o total, así como la captura o impresión de este documento están restringidas por nuestras políticas y el acuerdo transaccional de renta.";
}

// Dividir el texto por saltos de línea para renderizar párrafos estilizados
$parrafos = preg_split('/\r\n|\r|\n/', $textoCompleto);
?>

<style>
/* Protecciones anti-impresión (Tarea 15) */
@media print {
    body, html, * {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
}

/* Temas y Marcos del Visor (Tarea 15) */
.visor-container {
    transition: all 0.4s ease;
    min-height: 75vh;
    border-radius: 1.5rem;
    position: relative;
    overflow: hidden;
}

/* Tema 1: Cyber/Tech Oscuro (Por defecto) */
.theme-cyber {
    background: #0f172a;
    color: #e2e8f0;
    border: 2px solid #3b82f6;
    box-shadow: 0 0 30px rgba(59, 130, 246, 0.15);
}
.theme-cyber .chapter-title { color: #60a5fa; }

/* Tema 2: Pergamino Clásico / Histórico */
.theme-parchment {
    background: #fef3c7;
    color: #451a03;
    border: 3px double #d97706;
    box-shadow: 0 0 30px rgba(217, 119, 6, 0.2);
    font-family: 'Georgia', serif;
}
.theme-parchment .chapter-title { color: #92400e; border-bottom: 2px dashed #d97706; padding-bottom: 8px; }

/* Tema 3: Sepia / Lectura Cálida */
.theme-sepia {
    background: #f4ecd8;
    color: #5c4b37;
    border: 2px solid #c8b99e;
    box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
    font-family: 'Palatino Linotype', 'Book Antiqua', Palatino, serif;
}
.theme-sepia .chapter-title { color: #785e3a; }

/* Tema 4: Noche Profunda / AMOLED */
.theme-night {
    background: #000000;
    color: #a3a3a3;
    border: 1px solid #262626;
    box-shadow: none;
}
.theme-night .chapter-title { color: #d4d4d4; }

.reader-content {
    font-size: 1.15rem;
    line-height: 1.9;
    letter-spacing: 0.3px;
    user-select: none; /* Anti-copia */
    -webkit-user-select: none;
}

/* Capa de desenfoque al perder el foco de la ventana (Anti-Captura) */
.blur-shield {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(15px);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-align: center;
    padding: 2rem;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s ease;
}
.blur-shield.active {
    opacity: 1;
    pointer-events: auto;
}
</style>

<div class="container py-4" id="visorMainArea">
    <!-- Barra superior del visor -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 bg-dark bg-opacity-75 p-3 rounded-4 border border-secondary shadow">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>estandar/panel" class="btn btn-outline-custom rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;" title="Volver al Panel">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <span class="badge bg-primary text-white mb-1"><i class="bi bi-book-half me-1"></i> <?= htmlspecialchars(ucfirst($recurso->tipo ?? 'Libro')) ?></span>
                <span class="badge bg-dark border border-secondary text-info mb-1"><?= htmlspecialchars($recurso->categoria_nombre ?? 'General') ?></span>
                <h4 class="fw-bold text-light mb-0"><?= htmlspecialchars($recurso->titulo) ?></h4>
                <div class="small text-secondary"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($recurso->autor) ?> (<?= htmlspecialchars((string)($recurso->anio_publicacion ?? '')) ?>)</div>
            </div>
        </div>

        <!-- Opciones de TTS y Temas -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Selector de Temas / Marcos -->
            <div class="btn-group" role="group" aria-label="Marcos de Lectura">
                <button type="button" class="btn btn-sm btn-outline-info active" onclick="setTheme('cyber', this)" title="Tech / Cyber"><i class="bi bi-laptop"></i> Cyber</button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="setTheme('parchment', this)" title="Pergamino Histórico"><i class="bi bi-journal-text"></i> Pergamino</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setTheme('sepia', this)" title="Sepia"><i class="bi bi-sun"></i> Sepia</button>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="setTheme('night', this)" title="Modo Noche"><i class="bi bi-moon-stars"></i> Noche</button>
            </div>

            <!-- Botones Text-To-Speech (Voz Alta) -->
            <div class="d-flex align-items-center gap-1 bg-dark p-1 rounded-pill border border-secondary">
                <button type="button" id="btnSpeak" class="btn btn-sm btn-gradient-primary rounded-pill px-3 d-flex align-items-center gap-1" onclick="toggleSpeech()">
                    <i class="bi bi-volume-up-fill fs-6" id="speakIcon"></i> <span id="speakText">Escuchar Voz Alta</span>
                </button>
                <button type="button" id="btnStopSpeak" class="btn btn-sm btn-outline-danger rounded-circle p-1 d-none align-items-center justify-content-center" style="width: 30px; height: 30px;" onclick="stopSpeech()" title="Detener Lectura">
                    <i class="bi bi-stop-fill"></i>
                </button>
                <select id="ttsRate" class="form-select form-select-sm bg-dark text-light border-0 py-0" style="width: auto; font-size: 0.8rem;" onchange="changeSpeechRate()">
                    <option value="0.8">0.8x</option>
                    <option value="1.0" selected>1.0x (Normal)</option>
                    <option value="1.25">1.25x</option>
                    <option value="1.5">1.5x</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Aviso sobre estado y lectura iniciada -->
    <div class="alert bg-dark border border-secondary text-light rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 small">
        <div>
            <i class="bi bi-shield-lock-fill text-info me-2 fs-5"></i>
            <span><strong>Protección Activa SGBV:</strong> Este material ha sido registrado como leído (`ha_leido = 1`). La devolución anticipada y captura de pantalla están restringidas para resguardar la propiedad intelectual.</span>
        </div>
        <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check2-all me-1"></i> Sesión Segura</span>
    </div>

    <!-- Contenedor del Visor / Marco Personalizado -->
    <div id="visorBox" class="visor-container theme-cyber p-4 p-md-5">
        <!-- Escudo anti-captura al perder foco -->
        <div class="blur-shield" id="blurShield">
            <i class="bi bi-eye-slash-fill display-1 text-warning mb-3"></i>
            <h3 class="fw-bold">Protección de Lectura Activada</h3>
            <p class="mb-0 text-light max-w-md">Por motivos de seguridad de derechos de autor y anti-captura, el contenido de la biblioteca digital se oculta cuando la ventana pierde el foco o se detecta un atajo del sistema.</p>
            <span class="badge bg-primary mt-3 px-3 py-2">Haz clic de nuevo en esta ventana para continuar tu lectura</span>
        </div>

        <!-- Contenido principal de lectura -->
        <div class="reader-content mx-auto" id="readerContentArea" style="max-width: 800px;">
            <h2 class="chapter-title fw-bold mb-4 text-center"><?= htmlspecialchars($recurso->titulo) ?></h2>
            <div class="text-center text-secondary small mb-5">
                <span>Por <?= htmlspecialchars($recurso->autor) ?></span> • <span><?= htmlspecialchars($recurso->categoria_nombre ?? 'Catálogo SGBV') ?></span>
            </div>

            <?php if (!empty($recurso->archivo_pdf) && file_exists(__DIR__ . '/../../public/uploads/pdf/' . $recurso->archivo_pdf)): ?>
                <div class="mb-5 p-4 bg-dark bg-opacity-75 rounded-4 border border-info shadow-lg text-center">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <span class="badge bg-primary px-3 py-2 fs-6"><i class="bi bi-journal-text me-1"></i> Documento Digital Oficial</span>
                        <span class="text-info small"><i class="bi bi-shield-check me-1"></i> Lectura digital protegida SGBV</span>
                    </div>
                    <!-- Visor Digital Incrustado -->
                    <iframe src="<?= BASE_URL ?>public/uploads/pdf/<?= htmlspecialchars($recurso->archivo_pdf) ?>#toolbar=0" class="w-100 rounded-3 border border-secondary shadow" style="height: 780px; background: #fff;"></iframe>
                </div>
            <?php elseif (!empty($recurso->archivo_pdf)): ?>
                <div class="mb-5 p-3 bg-dark bg-opacity-50 rounded-3 border border-secondary text-center">
                    <i class="bi bi-book-half text-info fs-1"></i>
                    <p class="text-light mt-2 mb-1">Este recurso digital incluye el material interactivo de lectura en línea.</p>
                    <div class="small text-info"><i class="bi bi-lock me-1"></i> Visualización e interacción en línea protegida dentro del reproductor SGBV.</div>
                </div>
            <?php endif; ?>

            <!-- Párrafos de lectura -->
            <div id="textParagraphs">
                <?php foreach ($parrafos as $p): ?>
                    <?php $linea = trim($p); ?>
                    <?php if (!empty($linea)): ?>
                        <p class="mb-4"><?= htmlspecialchars($linea) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <hr class="border-secondary my-5">
            <div class="text-center text-secondary small">
                <i class="bi bi-journal-check me-1"></i> Has llegado al final de la vista de lectura en línea de <strong><?= htmlspecialchars($recurso->titulo) ?></strong>.
            </div>
        </div>
    </div>
</div>

<script>
// ==========================================
// 1. GESTIÓN DE TEMAS / MARCOS DE LECTURA
// ==========================================
function setTheme(themeName, btnElement) {
    const box = document.getElementById('visorBox');
    box.className = `visor-container theme-${themeName} p-4 p-md-5`;

    // Actualizar botones activos
    const btns = btnElement.parentElement.querySelectorAll('button');
    btns.forEach(b => {
        b.classList.remove('active', 'btn-info', 'btn-warning', 'btn-secondary', 'btn-light');
        if (b.getAttribute('title').includes('Cyber')) b.classList.add('btn-outline-info');
        if (b.getAttribute('title').includes('Pergamino')) b.classList.add('btn-outline-warning');
        if (b.getAttribute('title').includes('Sepia')) b.classList.add('btn-outline-secondary');
        if (b.getAttribute('title').includes('Noche')) b.classList.add('btn-outline-light');
    });

    btnElement.classList.add('active');
}

// ==========================================
// 2. TEXT-TO-SPEECH (SÍNTESIS DE VOZ ALTA)
// ==========================================
let synth = window.speechSynthesis;
let utterance = null;
let isSpeaking = false;
let isPaused = false;

function toggleSpeech() {
    if (!synth) {
        alert("Tu navegador no soporta la función de Text-to-Speech (Síntesis de voz).");
        return;
    }

    if (isSpeaking && !isPaused) {
        synth.pause();
        isPaused = true;
        document.getElementById('speakIcon').className = 'bi bi-play-fill fs-6';
        document.getElementById('speakText').innerText = 'Continuar Lectura';
        return;
    }

    if (isSpeaking && isPaused) {
        synth.resume();
        isPaused = false;
        document.getElementById('speakIcon').className = 'bi bi-pause-fill fs-6';
        document.getElementById('speakText').innerText = 'Pausar Voz Alta';
        return;
    }

    // Iniciar nueva lectura desde el inicio
    const textToRead = document.getElementById('textParagraphs').innerText;
    utterance = new SpeechSynthesisUtterance(textToRead);
    utterance.lang = 'es-ES'; // Español por defecto
    utterance.rate = parseFloat(document.getElementById('ttsRate').value || 1.0);

    utterance.onstart = () => {
        isSpeaking = true;
        isPaused = false;
        document.getElementById('speakIcon').className = 'bi bi-pause-fill fs-6';
        document.getElementById('speakText').innerText = 'Pausar Voz Alta';
        document.getElementById('btnStopSpeak').classList.remove('d-none');
        document.getElementById('btnStopSpeak').classList.add('d-flex');
    };

    utterance.onend = () => {
        stopSpeech();
    };

    utterance.onerror = () => {
        stopSpeech();
    };

    synth.cancel(); // Limpiar cola previa
    synth.speak(utterance);
}

function stopSpeech() {
    if (synth) synth.cancel();
    isSpeaking = false;
    isPaused = false;
    document.getElementById('speakIcon').className = 'bi bi-volume-up-fill fs-6';
    document.getElementById('speakText').innerText = 'Escuchar Voz Alta';
    const btnStop = document.getElementById('btnStopSpeak');
    if (btnStop) {
        btnStop.classList.add('d-none');
        btnStop.classList.remove('d-flex');
    }
}

function changeSpeechRate() {
    if (isSpeaking && synth && utterance) {
        // Reiniciar con la nueva velocidad desde el texto actual o ajustar si el motor lo permite
        const wasPaused = isPaused;
        stopSpeech();
        toggleSpeech();
        if (wasPaused) {
            synth.pause();
            isPaused = true;
            document.getElementById('speakIcon').className = 'bi bi-play-fill fs-6';
            document.getElementById('speakText').innerText = 'Continuar Lectura';
        }
    }
}

// Detener voz al salir de la página
window.addEventListener('beforeunload', stopSpeech);

// ==========================================
// 3. SISTEMA ANTI-CAPTURA Y ANTI-IMPRESIÓN
// ==========================================
// Bloquear menú contextual (clic derecho)
document.addEventListener('contextmenu', function(e) {
    if (e.target.closest('#visorBox')) {
        e.preventDefault();
        return false;
    }
});

// Bloquear atajos de teclado de captura o impresión
document.addEventListener('keydown', function(e) {
    // Ctrl+P (Imprimir), Ctrl+S (Guardar), Ctrl+C/X (Copiar/Cortar), PrintScreen, F12
    if (
        (e.ctrlKey && (e.key === 'p' || e.key === 'P' || e.key === 's' || e.key === 'S' || e.key === 'c' || e.key === 'C')) ||
        e.key === 'PrintScreen' ||
        e.key === 'F12'
    ) {
        e.preventDefault();
        activateBlurShield();
        return false;
    }
});

// Activar escudo de desenfoque al perder el foco de la ventana
const blurShield = document.getElementById('blurShield');

function activateBlurShield() {
    if (blurShield) blurShield.classList.add('active');
}

function deactivateBlurShield() {
    if (blurShield) blurShield.classList.remove('active');
}

window.addEventListener('blur', function() {
    activateBlurShield();
});

window.addEventListener('focus', function() {
    deactivateBlurShield();
});

// También quitar escudo al hacer clic dentro del contenedor
document.getElementById('visorBox').addEventListener('click', function() {
    deactivateBlurShield();
});
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
