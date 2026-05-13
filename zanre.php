<?php
// pages/zanre.php – Správa žánrov (CRUD)
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Žánre';
$chyby = [];
$editovat = null;

// ---------- DELETE zanru ----------
if (isset($_GET['vymazat']) && (int)$_GET['vymazat'] > 0) {
    $did = (int)$_GET['vymazat'];
    $pocet = (int) mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) c FROM hry WHERE zanr_id = $did")
    )['c'];

    if ($pocet > 0) {
        $chyby[] = "Žáner sa nedá vymazať – obsahuje $pocet " . ($pocet === 1 ? 'hru' : ($pocet < 5 ? 'hry' : 'hier')) . '.';
    } else {
        mysqli_query($conn, "DELETE FROM zanre WHERE id = $did");
        header('Location: zanre.php?msg=vymazany');
        exit;
    }
}

// ---------- NACITANIE na editaciu ----------
if (isset($_GET['upravit']) && (int)$_GET['upravit'] > 0) {
    $eid = (int)$_GET['upravit'];
    $editovat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM zanre WHERE id = $eid"));
}

// ---------- ULOZENIE (INSERT alebo UPDATE) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazov   = trim($_POST['nazov']   ?? '');
    $popis   = trim($_POST['popis']   ?? '');
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if ($nazov === '') {
        $chyby[] = 'Názov žánru je povinný.';
    } elseif (mb_strlen($nazov) > 100) {
        $chyby[] = 'Názov žánru môže mať max. 100 znakov.';
    }

    if (empty($chyby)) {
        $n = mysqli_real_escape_string($conn, $nazov);
        $p = mysqli_real_escape_string($conn, $popis);

        if ($edit_id > 0) {
            // UPDATE
            mysqli_query($conn, "UPDATE zanre SET nazov='$n', popis='$p' WHERE id=$edit_id");
            header('Location: zanre.php?msg=upraveny');
            exit;
        } else {
            // Kontrola duplicity
            $dup = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM zanre WHERE nazov='$n'"));
            if ($dup) {
                $chyby[] = "Žáner „$nazov" už existuje.";
            } else {
                mysqli_query($conn, "INSERT INTO zanre (nazov, popis) VALUES ('$n','$p')");
                header('Location: zanre.php?msg=pridany');
                exit;
            }
        }
    }

    // Po chybe zachovame formular
    if ($edit_id > 0) {
        $editovat = ['id' => $edit_id, 'nazov' => $_POST['nazov'] ?? '', 'popis' => $_POST['popis'] ?? ''];
    }
}

// ---------- Zoznam zanrov + pocet hier ----------
$zanre = mysqli_query($conn, "
    SELECT z.*, COUNT(h.id) AS pocet_hier
    FROM zanre z
    LEFT JOIN hry h ON z.id = h.zanr_id
    GROUP BY z.id
    ORDER BY z.nazov
");

$msg = $_GET['msg'] ?? '';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Navigacia + nazov -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="../index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h2 class="mb-0 fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Správa žánrov</h2>
</div>

<!-- Flash spravy -->
<?php if ($msg === 'pridany'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>Žáner bol pridaný.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($msg === 'upraveny'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i>Žáner bol aktualizovaný.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php elseif ($msg === 'vymazany'): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-trash me-1"></i>Žáner bol vymazaný.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Chyby -->
<?php if (!empty($chyby)): ?>
<div class="alert alert-danger">
    <?php foreach ($chyby as $ch): ?>
        <div><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($ch) ?></div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ===== FORMULAR ===== -->
    <div class="col-md-4">
        <div class="form-card">
            <h5 class="mb-3 fw-bold">
                <?php if ($editovat): ?>
                    <i class="bi bi-pencil me-1 text-primary"></i>Upraviť žáner
                <?php else: ?>
                    <i class="bi bi-plus-circle me-1 text-primary"></i>Pridať žáner
                <?php endif; ?>
            </h5>

            <form method="POST" action="zanre.php">
                <?php if ($editovat): ?>
                    <input type="hidden" name="edit_id" value="<?= (int)$editovat['id'] ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Názov <span class="text-danger">*</span></label>
                    <input type="text" name="nazov" class="form-control"
                           value="<?= htmlspecialchars($editovat['nazov'] ?? '') ?>"
                           placeholder="napr. RPG" maxlength="100" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Popis</label>
                    <textarea name="popis" class="form-control" rows="2"
                              placeholder="Krátky popis žánru..."><?= htmlspecialchars($editovat['popis'] ?? '') ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy me-1"></i><?= $editovat ? 'Uložiť' : 'Pridať' ?>
                    </button>
                    <?php if ($editovat): ?>
                        <a href="zanre.php" class="btn btn-outline-secondary">Zrušiť</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== TABULKA ===== -->
    <div class="col-md-8">
        <div class="form-card">
            <h5 class="mb-3 fw-bold">
                Zoznam žánrov
                <span class="badge bg-secondary fw-normal ms-1"><?= mysqli_num_rows($zanre) ?></span>
            </h5>

            <div class="table-responsive">
                <table class="table table-hover table-zanre align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Žáner</th>
                            <th>Popis</th>
                            <th class="text-center">Hier</th>
                            <th style="width:100px"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($z = mysqli_fetch_assoc($zanre)): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($z['nazov']) ?></td>
                            <td class="text-muted small">
                                <?php
                                    $p = $z['popis'] ?? '';
                                    echo $p !== ''
                                        ? htmlspecialchars(mb_substr($p, 0, 55)) . (mb_strlen($p) > 55 ? '…' : '')
                                        : '–';
                                ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary rounded-pill"><?= $z['pocet_hier'] ?></span>
                            </td>
                            <td class="text-end">
                                <a href="zanre.php?upravit=<?= $z['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary me-1"
                                   title="Upraviť">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ($z['pocet_hier'] == 0): ?>
                                    <a href="zanre.php?vymazat=<?= $z['id'] ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       title="Vymazať"
                                       onclick="return confirm('Vymazať žáner „<?= htmlspecialchars(addslashes($z['nazov'])) ?>"?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-danger" disabled
                                            title="Žáner má hry, nedá sa vymazať">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
