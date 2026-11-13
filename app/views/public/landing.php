<?php
/**
 * Página principal pública (Landing Page — Módulo Público / Visitantes).
 * Variables: $niveles, $isLogged, $user.
 */
$nivelesLista = is_array($niveles ?? null) ? $niveles : [];
?>
<header class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: #0b1f3a; border-bottom: 1px solid rgba(255,255,255,0.1);">
    <div class="container py-2">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="<?= e(url('landing')) ?>">
            <img src="<?= e(asset('img/logo-mark.svg')) ?>" alt="Logo Anglish" width="40" height="40">
            <div>
                <span class="d-block lh-1 fs-5 text-white">Anglish</span>
                <small class="text-white" style="font-size: 0.72rem; font-weight: 400;">Academia de inglés</small>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar"
                aria-controls="publicNavbar" aria-expanded="false" aria-label="Menú de navegación">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="publicNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-3">
                <li class="nav-item"><a class="nav-link text-white" href="#inicio">Inicio</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#quienes-somos">¿Quiénes somos?</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#programas">Programas (A1–C1)</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#objetivos">Metodología y Objetivos</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="#entrevista">Agendar Entrevista</a></li>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($isLogged)): ?>
                    <a href="<?= e(url('dashboard')) ?>" class="btn btn-success btn-sm px-3 fw-semibold">
                        <i class="bi bi-grid-1x2-fill me-1"></i> Ir a mi Panel (<?= e((string) ($user['rol'] ?? '')) ?>)
                    </a>
                <?php else: ?>
                    <span class="badge bg-light text-dark d-none d-xl-inline-block me-1">
                        <i class="bi bi-eye me-1"></i> Modo Visitante
                    </span>
                    <a href="<?= e(url('login')) ?>" class="btn btn-primary btn-sm px-3 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main id="inicio">
    <div class="container mt-3">
        <?php require APP_PATH . '/views/partials/flash.php'; ?>
    </div>

    <!-- HERO SECTION -->
    <section class="py-5 text-white" style="background: linear-gradient(135deg, #0b1f3a 0%, #153969 60%, #0f4c81 100%);">
        <div class="container py-lg-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="badge rounded-pill px-3 py-2 mb-3" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.4);">
                        <i class="bi bi-patch-check-fill me-1"></i> Formación alineada con el Marco Común Europeo (MCER)
                    </span>
                    <h1 class="display-4 fw-bold mb-3 text-white">Domina el inglés con claridad, fluidez y propósito real.</h1>
                    <p class="lead text-white mb-4">
                        En <strong>Anglish — Academia de inglés</strong> transformamos el aprendizaje del idioma mediante
                        grupos reducidos, seguimiento personalizado por competencias y acompañamiento docente continuo.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#programas" class="btn btn-light btn-lg px-4 fw-semibold text-dark">
                            Conocer niveles A1 – C1 <i class="bi bi-arrow-down-short"></i>
                        </a>
                        <a href="#entrevista" class="btn btn-outline-light btn-lg px-4">
                            <i class="bi bi-calendar2-check me-1"></i> Solicitar entrevista diagnóstica
                        </a>
                        <a href="<?= e(base_url('public/downloads/anglish-app.apk')) ?>" class="btn btn-success btn-lg px-4" download>
                            <i class="bi bi-android2 me-1"></i> Descargar App Android
                        </a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4 p-4 text-dark bg-white">
                        <h3 class="h5 fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-award-fill text-primary fs-4"></i> ¿Por qué estudiar en Anglish?
                        </h3>
                        <ul class="list-unstyled mb-0 d-grid gap-3">
                            <li class="d-flex gap-3 align-items-start">
                                <span class="badge bg-primary-subtle text-primary rounded-3 p-2"><i class="bi bi-people-fill fs-5"></i></span>
                                <div>
                                    <strong class="d-block">Grupos personalizados</strong>
                                    <small class="text-muted">Máximo 12 a 15 estudiantes por aula para garantizar práctica conversacional en cada sesión.</small>
                                </div>
                            </li>
                            <li class="d-flex gap-3 align-items-start">
                                <span class="badge bg-success-subtle text-success rounded-3 p-2"><i class="bi bi-laptop fs-5"></i></span>
                                <div>
                                    <strong class="d-block">Plataforma Web & App Móvil</strong>
                                    <small class="text-muted">Entrega tareas, consulta tus calificaciones, revisa alertas y monitorea tu asistencia en tiempo real.</small>
                                </div>
                            </li>
                            <li class="d-flex gap-3 align-items-start">
                                <span class="badge bg-warning-subtle text-warning-emphasis rounded-3 p-2"><i class="bi bi-clock-history fs-5"></i></span>
                                <div>
                                    <strong class="d-block">Horarios flexibles</strong>
                                    <small class="text-muted">Jornadas de mañana, tarde, noche y sabatina adaptadas a tu ritmo de estudio o trabajo.</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN: ¿QUIÉNES SOMOS? -->
    <section id="quienes-somos" class="py-5 bg-white">
        <div class="container py-4">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-8">
                    <span class="text-primary fw-bold text-uppercase small">Identidad Institucional</span>
                    <h2 class="display-6 fw-bold mt-1">¿Quiénes somos?</h2>
                    <p class="text-muted">
                        Somos una institución educativa dedicada a la enseñanza integral del idioma inglés en Colombia,
                        comprometida con el desarrollo comunicativo, académico y laboral de nuestros estudiantes.
                    </p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                        <div class="mb-3 text-primary fs-2"><i class="bi bi-compass-fill"></i></div>
                        <h3 class="h5 fw-bold">Nuestra Misión</h3>
                        <p class="text-muted mb-0">
                            Formar ciudadanos bilingües capaces de comunicarse con seguridad en contextos reales,
                            integrando pedagogía comunicativa activa y herramientas tecnológicas de seguimiento académico.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                        <div class="mb-3 text-success fs-2"><i class="bi bi-eye-fill"></i></div>
                        <h3 class="h5 fw-bold">Nuestra Visión</h3>
                        <p class="text-muted mb-0">
                            Consolidarnos como la academia líder en innovación pedagógica y transparencia formativa,
                            reconocida por la excelencia de sus egresados en estándares internacionales MCER.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4">
                        <div class="mb-3 text-info fs-2"><i class="bi bi-bullseye"></i></div>
                        <h3 class="h5 fw-bold">¿De qué trata la academia?</h3>
                        <p class="text-muted mb-0">
                            Nuestro modelo combina clases dinámicas presenciales y sincrónicas con evaluación formativa continua:
                            talleres prácticos, proyectos de speaking, quices interactivos y tutorías de refuerzo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECCIÓN: PROGRAMAS Y NIVELES -->
    <section id="programas" class="py-5" style="background: #f4f7fb;">
        <div class="container py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
                <div>
                    <span class="text-primary fw-bold text-uppercase small">Ruta de Aprendizaje</span>
                    <h2 class="h1 fw-bold mt-1 mb-0">Nuestros Programas Académicos</h2>
                </div>
                <p class="text-muted mb-0">Estructura curricular de 5 niveles basados en el Marco Común Europeo (A1 a C1).</p>
            </div>

            <div class="row g-4">
                <?php foreach ($nivelesLista as $nivel): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary fs-6 px-3 py-2 rounded-3">Nivel <?= e((string) $nivel['codigo']) ?></span>
                                <span class="text-muted small"><i class="bi bi-clock me-1"></i>80 Horas académicas</span>
                            </div>
                            <h3 class="h5 fw-bold"><?= e((string) $nivel['nombre']) ?></h3>
                            <p class="text-muted small flex-grow-1"><?= e((string) $nivel['descripcion']) ?></p>
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center small text-secondary">
                                <span><i class="bi bi-check2-circle text-success me-1"></i>Certificable</span>
                                <a href="#entrevista" class="fw-semibold text-decoration-none">Postularme <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- SECCIÓN: OBJETIVOS Y METODOLOGÍA -->
    <section id="objetivos" class="py-5 bg-white">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="text-primary fw-bold text-uppercase small">Metodología & Objetivos</span>
                    <h2 class="fw-bold mt-1 mb-3">Objetivos de formación en Anglish</h2>
                    <p class="text-muted">
                        Diseñamos cada curso para que el estudiante alcance metas medibles en las cuatro habilidades
                        lingüísticas fundamentales:
                    </p>
                    <div class="row g-3 mt-2">
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="d-block mb-1"><i class="bi bi-mic-fill text-primary me-1"></i> Speaking & Fluency</strong>
                                <small class="text-muted">Debates, presentaciones orales y simulaciones de entrevistas laborales.</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="d-block mb-1"><i class="bi bi-headphones text-success me-1"></i> Listening Activo</strong>
                                <small class="text-muted">Comprensión auditiva con acentos reales y situaciones cotidianas.</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="d-block mb-1"><i class="bi bi-journal-richtext text-warning me-1"></i> Reading Crítico</strong>
                                <small class="text-muted">Análisis de artículos, textos académicos y vocabulario técnico.</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 border rounded-3 h-100">
                                <strong class="d-block mb-1"><i class="bi bi-pencil-square text-info me-1"></i> Writing Estructurado</strong>
                                <small class="text-muted">Redacción de ensayos, correos corporativos y reportes con retroalimentación docente.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORMULARIO DE ENTREVISTA PARA VISITANTES -->
                <div class="col-lg-6" id="entrevista">
                    <div class="card border-0 shadow rounded-4 p-4" style="background: #f8fafc;">
                        <span class="badge bg-primary-subtle text-primary align-self-start mb-2 px-3 py-1">Admisiones abiertas</span>
                        <h3 class="h4 fw-bold mb-1">Agenda tu entrevista de clasificación</h3>
                        <p class="text-muted small mb-4">
                            ¿Quieres saber en qué nivel empezar? Déjanos tus datos y un coordinador académico programará tu entrevista diagnóstica gratuita.
                        </p>

                        <form action="<?= e(url('public/entrevista')) ?>" method="post" class="row g-3">
                            <?= csrf_field() ?>
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold">Nombre completo *</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej. Laura Camila Gómez" required maxlength="120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Correo electrónico *</label>
                                <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required maxlength="120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Teléfono / WhatsApp *</label>
                                <input type="text" name="telefono" class="form-control" placeholder="300 000 0000" required maxlength="25">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Nivel de interés</label>
                                <select name="nivel_id" class="form-select">
                                    <?php foreach ($nivelesLista as $niv): ?>
                                        <option value="<?= (int) $niv['id'] ?>"><?= e((string) $niv['codigo'] . ' — ' . (string) $niv['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Modalidad</label>
                                <select name="modalidad" class="form-select">
                                    <option value="VIRTUAL">Virtual (Meet)</option>
                                    <option value="PRESENCIAL">Presencial</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Fecha preferida</label>
                                <input type="date" name="fecha_preferida" class="form-control" value="<?= e(date('Y-m-d', strtotime('+2 days'))) ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Comentarios o disponibilidad horaria</label>
                                <textarea name="observaciones" class="form-control" rows="2" placeholder="Cuéntanos si tienes experiencia previa o en qué jornada prefieres estudiar..."></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="bi bi-send-check me-1"></i> Solicitar entrevista de nivelación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="py-4 text-white" style="background: #0b1f3a;">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <strong class="text-white">Anglish — Academia de inglés</strong>
            <span class="ms-2 small">Calle 45 # 12-34, Bogotá · contacto@anglish.edu.co</span>
        </div>
        <div class="small">
            <span>Modo Visitante Activo · </span>
            <a href="<?= e(url('login')) ?>" class="text-white text-decoration-underline">Acceso para Usuarios Registrados</a>
        </div>
    </div>
</footer>
