<?php
// ================================================
// pages/detail.php – Detail hry (READ)
// ================================================
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: ../index.php'); exit; }

$hra = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT h.*, z.nazov AS zanr_nazov
        FROM hry h
        LEFT JOIN zanre z ON h.zanr_id = z.id
        WHERE h.id = $id
    ")
);
if (!$hra) { header('Location: ../index.php'); exit; }

$pageTitle = $hra['nazov'];
$msg = $_GET['msg'] ?? '';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Flash spravy -->
<?php if ($msg === 'pridana'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>Hra bola úspešne pridaná!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($msg === 'upravena'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>Hra bola úspešne aktualizovaná!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tlacidlo spat -->
<div class="mb-3">
    <a href="../index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Späť na zoznam
    </a>
</div>

<!-- Hlavicka detailu -->
<div class="detail-hero">
    <div class="detail-icon">
        <i class="bi bi-controller"></i>
    </div>
    <div class="flex-grow-1 min-w-0">
        <h1 class="h2 mb-1 fw-bold"><?= htmlspecialchars($hra['nazov']) ?></h1>
        <p class="mb-2 opacity-75 small">
            <i class="bi bi-building me-1"></i><?= htmlspecialchars($hra['vyvojar']) ?>
            <?php if ($hra['rok_vydania']): ?>
                &middot; <?= $hra['rok_vydania'] ?>
            <?php endif; ?>
        </p>
        <div class="d-flex flex-wrap gap-2">
            <?php if ($hra['zanr_nazov']): ?>
                <span class="badge bg-primary"><?= htmlspecialchars($hra['zanr_nazov']) ?></span>
            <?php endif; ?>
            <?php if ($hra['zahrana']): ?>
                <span class="badge bg-success"><i class="bi bi-check me-1"></i>Odohraná</span>
            <?php else: ?>
                <span class="badge bg-secondary">Neodohraná</span>
            <?php endif; ?>
            <?php if ($hra['platforma']): ?>
                <span class="badge bg-info text-dark">
                    <i class="bi bi-display me-1"></i><?= htmlspecialchars($hra['platforma']) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <!-- Akcie (desktop) -->
    <div class="text-end d-none d-md-flex flex-column gap-2" style="flex-shrink:0">
        <a href="upravit.php?id=<?= $id ?>" class="btn btn-light btn-sm">
            <i class="bi bi-pencil me-1"></i>Upraviť
        </a>
        <a href="vymazat.php?id=<?= $id ?>" class="btn btn-outline-light btn-sm"
           onclick="return confirm('Naozaj vymazať túto hru?')">
            <i class="bi bi-trash me-1"></i>Vymazať
        </a>
    </div>
</div>

<!-- Obsah detailu -->
<div class="row g-4">

    <!-- Popis -->
    <div class="col-md-7">
        <?php if ($hra['popis']): ?>
        <div class="form-card h-100">
            <h5 class="mb-3"><i class="bi bi-file-text me-2 text-primary"></i>Popis</h5>
            <p class="mb-0" style="line-height:1.7"><?= nl2br(htmlspecialchars($hra['popis'])) ?></p>
        </div>
        <?php else: ?>
        <div class="form-card h-100 text-muted text-center py-5">
            <i class="bi bi-file-text" style="font-size:2rem;opacity:.3"></i>
            <p class="mt-2 mb-0 small">Žiadny popis.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info panel -->
    <div class="col-md-5">
        <div class="form-card">
            <h5 class="mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Informácie</h5>
            <table class="table table-sm table-borderless mb-0 small">
                <?php if ($hra['platforma']): ?>
                <tr>
                    <td class="text-muted" style="width:40%">Platforma</td>
                    <td class="fw-semibold"><?= htmlspecialchars($hra['platforma']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($hra['zanr_nazov']): ?>
                <tr>
                    <td class="text-muted">Žáner</td>
                    <td><?= htmlspecialchars($hra['zanr_nazov']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($hra['cas_hrania']): ?>
                <tr>
                    <td class="text-muted">Čas hrania</td>
                    <td><i class="bi bi-clock-fill text-danger me-1"></i><?= $hra['cas_hrania'] ?> hodín</td>
                </tr>
                <?php endif; ?>
                <?php if ($hra['hodnotenie']): ?>
                <tr>
                    <td class="text-muted">Hodnotenie</td>
                    <td>
                        <span class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star<?= $i <= $hra['hodnotenie'] ? '-fill' : '' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <span class="text-muted">(<?= $hra['hodnotenie'] ?>/5)</span>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-muted">Stav</td>
                    <td>
                        <?php if ($hra['zahrana']): ?>
                            <span class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Odohraná</span>
                        <?php else: ?>
                            <span class="text-muted"><i class="bi bi-hourglass me-1"></i>Neodohraná</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">Pridaná</td>
                    <td><?= date('d.m.Y', strtotime($hra['vytvorene'])) ?></td>
                </tr>
                <?php if ($hra['vytvorene'] !== $hra['upravene']): ?>
                <tr>
                    <td class="text-muted">Naposledy upravená</td>
                    <td><?= date('d.m.Y', strtotime($hra['upravene'])) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- Akcie (mobile) -->
        <div class="d-md-none mt-3 d-flex gap-2">
            <a href="upravit.php?id=<?= $id ?>" class="btn btn-primary flex-grow-1">
                <i class="bi bi-pencil me-1"></i>Upraviť
            </a>
            <a href="vymazat.php?id=<?= $id ?>" class="btn btn-outline-danger"
               onclick="return confirm('Naozaj vymazať?')">
                <i class="bi bi-trash"></i>
            </a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
