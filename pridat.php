<?php
// ================================================
// pages/pridat.php – Pridanie novej hry (CREATE)
// ================================================
require_once __DIR__ . '/../includes/db.php';

$pageTitle = 'Pridať hru';
$chyby = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Nacitanie a cistenie vstupov ---
    $nazov      = trim($_POST['nazov']      ?? '');
    $vyvojar    = trim($_POST['vyvojar']    ?? '');
    $rok        = trim($_POST['rok']        ?? '');
    $platforma  = trim($_POST['platforma']  ?? '');
    $zanr_id    = (int)($_POST['zanr_id']   ?? 0);
    $cas        = trim($_POST['cas']        ?? '');
    $popis      = trim($_POST['popis']      ?? '');
    $zahrana    = isset($_POST['zahrana'])  ? 1 : 0;
    $hodnotenie = (int)($_POST['hodnotenie'] ?? 0);

    // --- Validacia ---
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

    // --- Ulozenie do DB ---
    if (empty($chyby)) {
        $n  = mysqli_real_escape_string($conn, $nazov);
        $v  = mysqli_real_escape_string($conn, $vyvojar);
        $pl = mysqli_real_escape_string($conn, $platforma);
        $po = mysqli_real_escape_string($conn, $popis);
        $r  = $rok  !== '' ? "'$rok'"  : 'NULL';
        $z  = $zanr_id > 0 ? $zanr_id  : 'NULL';
        $c  = $cas  !== '' && (int)$cas > 0 ? (int)$cas : 'NULL';
        $h  = $hodnotenie > 0 ? $hodnotenie : 'NULL';

        $sql = "INSERT INTO hry (nazov, vyvojar, rok_vydania, platforma, zanr_id, cas_hrania, popis, zahrana, hodnotenie)
                VALUES ('$n','$v',$r,'$pl',$z,$c,'$po',$zahrana,$h)";

        if (mysqli_query($conn, $sql)) {
            $nid = mysqli_insert_id($conn);
            header("Location: detail.php?id=$nid&msg=pridana");
            exit;
        } else {
            $chyby[] = 'Chyba databázy: ' . mysqli_error($conn);
        }
    }
}

// Zachovanie hodnot po chybe
$f = [
    'nazov'      => $_POST['nazov']      ?? '',
    'vyvojar'    => $_POST['vyvojar']    ?? '',
    'rok'        => $_POST['rok']        ?? '',
    'platforma'  => $_POST['platforma']  ?? '',
    'zanr_id'    => (int)($_POST['zanr_id']   ?? 0),
    'cas'        => $_POST['cas']        ?? '',
    'popis'      => $_POST['popis']      ?? '',
    'zahrana'    => isset($_POST['zahrana']) ? 1 : 0,
    'hodnotenie' => (int)($_POST['hodnotenie'] ?? 0),
];

$zanre = mysqli_query($conn, "SELECT id, nazov FROM zanre ORDER BY nazov");
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
<div class="col-lg-8">

    <!-- Navigacia -->
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="../index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="mb-0 fw-bold"><i class="bi bi-plus-circle me-2 text-primary"></i>Pridať novú hru</h2>
    </div>

    <!-- Chyby -->
    <?php if (!empty($chyby)): ?>
    <div class="alert alert-danger">
        <strong><i class="bi bi-exclamation-triangle me-1"></i>Formulár obsahuje chyby:</strong>
        <ul class="mb-0 mt-2 ps-3">
            <?php foreach ($chyby as $ch): ?>
                <li><?= htmlspecialchars($ch) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Formular -->
    <div class="form-card">
        <form method="POST" action="pridat.php">

            <div class="row g-3">

                <!-- Nazov -->
                <div class="col-12">
                    <label class="form-label" for="nazov">Názov hry <span class="text-danger">*</span></label>
                    <input type="text" id="nazov" name="nazov" class="form-control"
                           value="<?= htmlspecialchars($f['nazov']) ?>"
                           placeholder="napr. The Witcher 3" maxlength="255" required>
                </div>

                <!-- Vyvojar + rok -->
                <div class="col-md-7">
                    <label class="form-label" for="vyvojar">Vývojár <span class="text-danger">*</span></label>
                    <input type="text" id="vyvojar" name="vyvojar" class="form-control"
                           value="<?= htmlspecialchars($f['vyvojar']) ?>"
                           placeholder="napr. CD Projekt Red" maxlength="255" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="rok">Rok vydania</label>
                    <input type="number" id="rok" name="rok" class="form-control"
                           value="<?= htmlspecialchars($f['rok']) ?>"
                           min="1970" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
                </div>

                <!-- Platforma + zanr -->
                <div class="col-md-7">
                    <label class="form-label" for="platforma">Platforma</label>
                    <input type="text" id="platforma" name="platforma" class="form-control"
                           value="<?= htmlspecialchars($f['platforma']) ?>"
                           placeholder="napr. PC, PS5, Xbox Series X" maxlength="150">
                </div>
                <div class="col-md-5">
                    <label class="form-label" for="zanr_id">Žáner</label>
                    <select id="zanr_id" name="zanr_id" class="form-select">
                        <option value="0">– Vybrať žáner –</option>
                        <?php while ($z = mysqli_fetch_assoc($zanre)): ?>
                            <option value="<?= $z['id'] ?>"
                                <?= $f['zanr_id'] == $z['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($z['nazov']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Cas hrania -->
                <div class="col-md-5">
                    <label class="form-label" for="cas">Čas hrania (hodiny)</label>
                    <input type="number" id="cas" name="cas" class="form-control"
                           value="<?= htmlspecialchars($f['cas']) ?>"
                           min="0" placeholder="napr. 50">
                </div>

                <!-- Hodnotenie -->
                <div class="col-md-7">
                    <label class="form-label" for="hodnotenie">Hodnotenie (1–5 ★)</label>
                    <select id="hodnotenie" name="hodnotenie" class="form-select">
                        <option value="0">– Bez hodnotenia –</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"
                                <?= $f['hodnotenie'] === $i ? 'selected' : '' ?>>
                                <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?> (<?= $i ?>/5)
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Popis -->
                <div class="col-12">
                    <label class="form-label" for="popis">Popis / poznámky</label>
                    <textarea id="popis" name="popis" class="form-control" rows="3"
                              placeholder="Krátky popis, dojmy z hry..."><?= htmlspecialchars($f['popis']) ?></textarea>
                </div>

                <!-- Checkbox odohrana -->
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="zahrana" name="zahrana" value="1"
                               <?= $f['zahrana'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="zahrana">
                            <i class="bi bi-check-circle-fill text-success me-1"></i>
                            Hru som odohral/a (zaradiť medzi odohraté)
                        </label>
                    </div>
                </div>

            </div><!-- /row -->

            <hr class="my-4">

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-floppy me-1"></i>Uložiť hru
                </button>
                <a href="../index.php" class="btn btn-outline-secondary">Zrušiť</a>
            </div>

        </form>
    </div>

</div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
