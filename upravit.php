<?php
// ================================================
// pages/upravit.php – Úprava hry (UPDATE)
// ================================================
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: ../index.php'); exit; }

// Nacitanie povodnych dat
$hra = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM hry WHERE id = $id"));
if (!$hra) { header('Location: ../index.php'); exit; }

$pageTitle = 'Upraviť – ' . $hra['nazov'];
$chyby = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nazov      = trim($_POST['nazov']      ?? '');
    $vyvojar    = trim($_POST['vyvojar']    ?? '');
    $rok        = trim($_POST['rok']        ?? '');
    $platforma  = trim($_POST['platforma']  ?? '');
    $zanr_id    = (int)($_POST['zanr_id']   ?? 0);
    $cas        = trim($_POST['cas']        ?? '');
    $popis      = trim($_POST['popis']      ?? '');
    $zahrana    = isset($_POST['zahrana'])  ? 1 : 0;
    $hodnotenie = (int)($_POST['hodnotenie'] ?? 0);

    // Validacia
    if ($nazov === '')   $chyby[] = 'Názov hry je povinný.';
    if ($vyvojar === '') $chyby[] = 'Vývojár je povinný.';
    if ($rok !== '' && (!ctype_digit($rok) || (int)$rok < 1970 || (int)$rok > (int)date('Y'))) {
        $chyby[] = 'Rok vydania musí byť číslo od 1970 do ' . date('Y') . '.';
    }
    if ($cas !== '' && (!ctype_digit($cas) || (int)$cas < 0)) {
        $chyby[] = 'Čas hrania musí byť kladné celé číslo.';
    }
    if ($hodnotenie !== 0 && ($hodnotenie < 1 || $hodnotenie > 5)) {
        $chyby[] = 'Hodnotenie musí byť od 1 do 5.';
    }

    if (empty($chyby)) {
        $n  = mysqli_real_escape_string($conn, $nazov);
        $v  = mysqli_real_escape_string($conn, $vyvojar);
        $pl = mysqli_real_escape_string($conn, $platforma);
        $po = mysqli_real_escape_string($conn, $popis);
        $r  = $rok  !== '' ? "'$rok'"  : 'NULL';
        $z  = $zanr_id > 0 ? $zanr_id  : 'NULL';
        $c  = ($cas !== '' && (int)$cas > 0) ? (int)$cas : 'NULL';
        $h  = $hodnotenie > 0 ? $hodnotenie : 'NULL';

        $sql = "UPDATE hry SET
                    nazov      = '$n',
                    vyvojar    = '$v',
                    rok_vydania = $r,
                    platforma  = '$pl',
                    zanr_id    = $z,
                    cas_hrania = $c,
                    popis      = '$po',
                    zahrana    = $zahrana,
                    hodnotenie = $h
                WHERE id = $id";

        if (mysqli_query($conn, $sql)) {
            header("Location: detail.php?id=$id&msg=upravena");
            exit;
        } else {
            $chyby[] = 'Chyba databázy: ' . mysqli_error($conn);
        }
    }

    // Po chybe prepiseme pole hodnotami z POST
    $hra = array_merge($hra, [
        'nazov'      => $_POST['nazov']      ?? $hra['nazov'],
        'vyvojar'    => $_POST['vyvojar']    ?? $hra['vyvojar'],
        'rok_vydania'=> $_POST['rok']        ?? $hra['rok_vydania'],
        'platforma'  => $_POST['platforma']  ?? $hra['platforma'],
        'zanr_id'    => (int)($_POST['zanr_id'] ?? $hra['zanr_id']),
        'cas_hrania' => $_POST['cas']        ?? $hra['cas_hrania'],
        'popis'      => $_POST['popis']      ?? $hra['popis'],
        'zahrana'    => isset($_POST['zahrana']) ? 1 : 0,
        'hodnotenie' => (int)($_POST['hodnotenie'] ?? $hra['hodnotenie']),
    ]);
}

$zanre = mysqli_query($conn, "SELECT id, nazov FROM zanre ORDER BY nazov");
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Upraviť hru</h2>
    </div>

    <?php if (!empty($chyby)): ?>
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Opravte chyby:</strong>
        <ul class="mb-0 mt-2 ps-3">
            <?php foreach ($chyby as $ch): ?>
                <li><?= htmlspecialchars($ch) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" action="upravit.php?id=<?= $id ?>">

            <div class="row g-3">

                <div class="col-12">
                    <label class="form-label">Názov hry <span class="text-danger">*</span></label>
                    <input type="text" name="nazov" class="form-control"
                           value="<?= htmlspecialchars($hra['nazov']) ?>"
                           maxlength="255" required>
                </div>

                <div class="col-md-7">
                    <label class="form-label">Vývojár <span class="text-danger">*</span></label>
                    <input type="text" name="vyvojar" class="form-control"
                           value="<?= htmlspecialchars($hra['vyvojar']) ?>"
                           maxlength="255" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Rok vydania</label>
                    <input type="number" name="rok" class="form-control"
                           value="<?= htmlspecialchars($hra['rok_vydania'] ?? '') ?>"
                           min="1970" max="<?= date('Y') ?>">
                </div>

                <div class="col-md-7">
                    <label class="form-label">Platforma</label>
                    <input type="text" name="platforma" class="form-control"
                           value="<?= htmlspecialchars($hra['platforma'] ?? '') ?>"
                           maxlength="150">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Žáner</label>
                    <select name="zanr_id" class="form-select">
                        <option value="0">– Bez žánru –</option>
                        <?php while ($z = mysqli_fetch_assoc($zanre)): ?>
                            <option value="<?= $z['id'] ?>"
                                <?= $hra['zanr_id'] == $z['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($z['nazov']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Čas hrania (hodiny)</label>
                    <input type="number" name="cas" class="form-control"
                           value="<?= htmlspecialchars($hra['cas_hrania'] ?? '') ?>"
                           min="0">
                </div>
                <div class="col-md-7">
                    <label class="form-label">Hodnotenie</label>
                    <select name="hodnotenie" class="form-select">
                        <option value="0">– Bez hodnotenia –</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"
                                <?= (int)$hra['hodnotenie'] === $i ? 'selected' : '' ?>>
                                <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?> (<?= $i ?>/5)
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">Popis / poznámky</label>
                    <textarea name="popis" class="form-control" rows="3"><?= htmlspecialchars($hra['popis'] ?? '') ?></textarea>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="zahrana" name="zahrana" value="1"
                               <?= $hra['zahrana'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="zahrana">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Hru som odohral/a
                        </label>
                    </div>
                </div>

            </div>

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-floppy me-1"></i>Uložiť zmeny
                </button>
                <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">Zrušiť</a>
            </div>

        </form>
    </div>

</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
