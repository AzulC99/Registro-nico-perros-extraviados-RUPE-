<?php
/**
 * RUPE CDMX — Página no encontrada
 * app/views/home/404.php
 */
declare(strict_types=1);

$title = $title ?? 'No encontrado';
?>
<section class="py-5">
    <div class="container text-center py-5">
        <p class="display-3 fw-bold text-guinda mb-2">404</p>
        <h1 class="h4 mb-3"><?= e($title) ?></h1>
        <p class="text-secondary mb-4">
            La página o el folio que buscas no existe, fue desactivado o la URL es incorrecta.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="<?= url('/') ?>" class="btn btn-guinda">
                <i class="bi bi-house me-1"></i> Inicio
            </a>
            <a href="<?= url('/buscar') ?>" class="btn btn-outline-guinda">
                <i class="bi bi-search me-1"></i> Buscar mascota
            </a>
        </div>
    </div>
</section>