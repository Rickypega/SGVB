<?php
$tituloPagina = 'Visor Digital de Lectura SGBV | ' . htmlspecialchars($recurso->titulo ?? 'Recurso');
require_once __DIR__ . '/../../layouts/header.php';

$pdfUrl = '';
if (!empty($recurso->archivo_pdf)) {
    $pdfUrl = BASE_URL . 'public/uploads/pdf/' . htmlspecialchars($recurso->archivo_pdf);
}
?>

<!-- Cargar PDF.js vía CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
    // Configurar worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
</script>

<style>
/* Protecciones anti-impresión (Tarea 15) */
@media print {
    body, html, * {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
}

.visor-container {
    transition: all 0.4s ease;
    min-height: 75vh;
    border-radius: 1.5rem;
    position: relative;
    overflow: hidden;
}

.theme-cyber { background: #0f172a; color: #e2e8f0; border: 2px solid #3b82f6; }
.theme-parchment { background: #fef3c7; color: #451a03; border: 3px double #d97706; font-family: 'Georgia', serif; }
.theme-sepia { background: #f4ecd8; color: #5c4b37; border: 2px solid #c8b99e; font-family: 'Palatino Linotype', Palatino, serif; }
.theme-night { background: #000000; color: #a3a3a3; border: 1px solid #262626; }

#pdfCanvas {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.blur-shield {
    position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(15px);
    z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center;
    color: #fff; text-align: center; padding: 2rem; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
}
.blur-shield.active { opacity: 1; pointer-events: auto; }
</style>

<div class="container py-4" id="visorMainArea">
    <!-- Barra superior del visor -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 bg-dark bg-opacity-75 p-3 rounded-4 border border-secondary shadow">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= BASE_URL ?>estandar/panel" class="btn btn-outline-custom rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;" title="Volver al Panel">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h4 class="fw-bold text-light mb-0"><?= htmlspecialchars($recurso->titulo) ?></h4>
                <div class="small text-secondary"><i class="bi bi-person me-1"></i> <?= htmlspecialchars($recurso->autor) ?></div>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <!-- Selector de Temas -->
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-info active" onclick="setTheme('cyber', this)" title="Tech / Cyber"><i class="bi bi-laptop"></i></button>
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="setTheme('parchment', this)" title="Pergamino"><i class="bi bi-journal-text"></i></button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setTheme('sepia', this)" title="Sepia"><i class="bi bi-sun"></i></button>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="setTheme('night', this)" title="Modo Noche"><i class="bi bi-moon-stars"></i></button>
            </div>

            <!-- Controles TTS -->
            <div class="d-flex align-items-center gap-2 bg-dark p-1 rounded-pill border border-secondary px-2">
                <select id="voiceSelect" class="form-select form-select-sm bg-dark text-light border-secondary rounded-pill" style="max-width: 180px; font-size: 0.8rem;">
                    <option value="">Cargando voces...</option>
                </select>
                <button type="button" id="btnSpeak" class="btn btn-sm btn-gradient-primary rounded-pill px-3" onclick="toggleSpeech()">
                    <i class="bi bi-volume-up-fill" id="speakIcon"></i> <span id="speakText">Leer</span>
                </button>
                <button type="button" id="btnStopSpeak" class="btn btn-sm btn-outline-danger rounded-circle p-1 d-none" style="width: 30px; height: 30px;" onclick="stopSpeech()">
                    <i class="bi bi-stop-fill"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Contenedor del Visor -->
    <div id="visorBox" class="visor-container theme-cyber p-3 p-md-4 text-center">
        <div class="blur-shield" id="blurShield">
            <i class="bi bi-eye-slash-fill display-1 text-warning mb-3"></i>
            <h3 class="fw-bold">Protección de Lectura</h3>
            <p>El contenido se oculta al perder el foco.</p>
        </div>

        <?php if ($pdfUrl): ?>
            <!-- Controles de Paginación -->
            <div class="d-flex justify-content-center align-items-center gap-3 mb-3">
                <button class="btn btn-primary" onclick="onPrevPage()"><i class="bi bi-chevron-left"></i> Anterior</button>
                <span>Página <span id="page_num"></span> de <span id="page_count"></span></span>
                <button class="btn btn-primary" onclick="onNextPage()">Siguiente <i class="bi bi-chevron-right"></i></button>
            </div>

            <!-- Canvas de PDF.js -->
            <canvas id="pdfCanvas"></canvas>
            
            <!-- Texto oculto extraído para el TTS -->
            <div id="textParagraphs" class="d-none"></div>
        <?php else: ?>
            <div class="alert alert-warning">No hay un documento PDF asociado a este recurso.</div>
            <div id="textParagraphs" class="d-none"><?= htmlspecialchars($recurso->descripcion ?? '') ?></div>
        <?php endif; ?>
    </div>
</div>

<script>
// 1. TEMAS
function setTheme(themeName, btnElement) {
    document.getElementById('visorBox').className = `visor-container theme-${themeName} p-3 p-md-4 text-center`;
    const btns = btnElement.parentElement.querySelectorAll('button');
    btns.forEach(b => {
        b.classList.remove('active', 'btn-info', 'btn-warning', 'btn-secondary', 'btn-light');
        b.classList.add('btn-outline-' + (b.getAttribute('title') === 'Tech / Cyber' ? 'info' : 'secondary'));
    });
    btnElement.classList.add('active');
}

// 2. PDF.JS
let pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 1.5,
    canvas = document.getElementById('pdfCanvas'),
    ctx = canvas ? canvas.getContext('2d') : null;

const pdfUrl = "<?= $pdfUrl ?>";

function renderPage(num) {
    pageRendering = true;
    pdfDoc.getPage(num).then(function(page) {
        var viewport = page.getViewport({scale: scale});
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        var renderContext = {
            canvasContext: ctx,
            viewport: viewport
        };
        var renderTask = page.render(renderContext);

        renderTask.promise.then(function() {
            pageRendering = false;
            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        });

        // Extraer texto para TTS
        page.getTextContent().then(function(textContent) {
            let textItems = textContent.items;
            let finalString = "";
            for (let i = 0; i < textItems.length; i++) {
                finalString += textItems[i].str + " ";
            }
            document.getElementById('textParagraphs').innerText = finalString;
            stopSpeech(); // Detener TTS si se cambia de página
        });
    });
    document.getElementById('page_num').textContent = num;
}

function queueRenderPage(num) {
    if (pageRendering) {
        pageNumPending = num;
    } else {
        renderPage(num);
    }
}

function onPrevPage() {
    if (pageNum <= 1) return;
    pageNum--;
    queueRenderPage(pageNum);
}

function onNextPage() {
    if (pageNum >= pdfDoc.numPages) return;
    pageNum++;
    queueRenderPage(pageNum);
}

if (pdfUrl) {
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        document.getElementById('page_count').textContent = pdfDoc.numPages;
        renderPage(pageNum);
    });
}

// 3. TTS (Texto a Voz) mejorado
let synth = window.speechSynthesis;
let utterance = null;
let isSpeaking = false;
let availableVoices = [];

function populateVoiceList() {
    // Filtrar voces en español (es-ES, es-MX, es-US, etc.)
    availableVoices = synth.getVoices().filter(v => v.lang.startsWith('es'));
    const voiceSelect = document.getElementById('voiceSelect');
    const previousSelection = voiceSelect.value; // Guardar selección previa
    
    voiceSelect.innerHTML = '';
    
    if(availableVoices.length === 0) {
        voiceSelect.innerHTML = '<option value="">Voz por defecto</option>';
        return;
    }

    availableVoices.forEach((voice, index) => {
        let option = document.createElement('option');
        
        // Identificar género por nombres comunes de voces de Windows/Google
        let genero = '';
        let nameLower = voice.name.toLowerCase();
        if (nameLower.includes('sabina') || nameLower.includes('helena') || nameLower.includes('laura') || nameLower.includes('mia') || nameLower.includes('paulina')) {
            genero = ' (Femenina)';
        } else if (nameLower.includes('pablo') || nameLower.includes('raul') || nameLower.includes('tomas') || nameLower.includes('jorge') || nameLower.includes('diego')) {
            genero = ' (Masculina)';
        } else if (nameLower.includes('google')) {
            genero = ' (Natural)';
        }
        
        option.textContent = voice.name.replace('Microsoft ', '').replace('Desktop', '') + genero;
        option.value = index;
        voiceSelect.appendChild(option);
    });
    
    // Restaurar la selección previa si sigue existiendo
    if (previousSelection !== "") {
        voiceSelect.value = previousSelection;
    }
}

// Inicializar voces
populateVoiceList();
if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = populateVoiceList;
}

function toggleSpeech() {
    if (!synth) return;

    if (isSpeaking) {
        stopSpeech();
        return;
    }

    const textToRead = document.getElementById('textParagraphs').innerText;
    if (!textToRead.trim()) return;

    utterance = new SpeechSynthesisUtterance(textToRead);
    utterance.lang = 'es-ES';
    
    // Configuración para una voz más humana y pausada
    utterance.rate = 0.9; // Velocidad de lectura cómoda
    utterance.pitch = 1.0;
    
    // Asignar voz seleccionada por el usuario
    const voiceSelect = document.getElementById('voiceSelect');
    if (availableVoices.length > 0 && voiceSelect.value !== "") {
        utterance.voice = availableVoices[voiceSelect.value];
    }
    
    utterance.onstart = () => {
        isSpeaking = true;
        document.getElementById('speakIcon').className = 'bi bi-stop-fill';
        document.getElementById('speakText').innerText = 'Detener';
        document.getElementById('btnStopSpeak').classList.remove('d-none');
    };
    utterance.onend = () => stopSpeech();
    
    synth.speak(utterance);
}

function stopSpeech() {
    if (synth) synth.cancel();
    isSpeaking = false;
    document.getElementById('speakIcon').className = 'bi bi-volume-up-fill';
    document.getElementById('speakText').innerText = 'Leer Página';
}
window.addEventListener('beforeunload', stopSpeech);

// 4. ANTI-CAPTURA
const blurShield = document.getElementById('blurShield');
window.addEventListener('blur', () => blurShield && blurShield.classList.add('active'));
window.addEventListener('focus', () => blurShield && blurShield.classList.remove('active'));
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
