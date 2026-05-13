<?php
// ================================================
// index.php – Zoznam hier (READ + filter)
// ================================================
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Prehľad hier';

// ---------- GET parametre (filter / hladanie) ----------
$hladaj   = isset($_GET['hladaj'])   ? mysqli_real_escape_string($conn, trim($_GET['hladaj']))   : '';
$zanr     = isset($_GET['zanr'])     ? (int)$_GET['zanr']                                        : 0;
$stav     = isset($_GET['stav'])     ? $_GET['stav']                                             : '';
$platforma = isset($_GET['platforma']) ? mysqli_real_escape_string($conn, trim($_GET['platforma'])) : '';

// ---------- Zostavenie WHERE ----------
$where = [];
if ($hladaj !== '')  $where[] = "(h.nazov LIKE '%$hladaj%' OR h.vyvojar LIKE '%$hladaj%')";
if ($zanr > 0)       $where[] = "h.zanr_id = $zanr";
if ($stav === '1')   $where[] = "h.zahrana = 1";
if ($stav === '0')   $where[] = "h.zahrana = 0";
if ($platforma !== '') $where[] = "h.platforma LIKE '%$platforma%'";

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ---------- Hlavny dopyt ----------
$sql = "
    SELECT h.*, z.nazov AS zanr_nazov
    FROM hry h
    LEFT JOIN zanre z ON h.zanr_id = z.id
    $where_sql
    ORDER BY h.vytvorene DESC
";
$vysledky = mysqli_query($conn, $sql);

// ---------- Statistiky (vzdy z celej tabulky) ----------
$s_celkom   = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM hry"))['c'];
$s_odohrane = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM hry WHERE zahrana=1"))['c'];
$s_cakaju   = $s_celkom - $s_odohrane;
$s_hodiny   = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(cas_hrania),0) c FROM hry WHERE zahrana=1"))['c'];
$s_zanre    = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM zanre"))['c'];

// ---------- Zoznam zanrov pre filter ----------
$zanre_opt = mysqli_query($conn, "SELECT id, nazov FROM zanre ORDER BY nazov");

// ---------- Flash sprava ----------
$msg = $_GET['msg'] ?? '';

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($msg === 'vymazana'): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-trash me-1"></i>Hra bola vymazaná.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ===== STATISTIKY ===== -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md col-lg">
        <div class="stat-card">
            <div class="num"><?= $s_celkom ?></div>
            <div class="lbl"><i class="bi bi-controller me-1"></i>Celkom hier</div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="stat-card" style="border-top-color:var(--success)">
            <div class="num" style="color:var(--success)"><?= $s_odohrane ?></div>
            <div class="lbl"><i class="bi bi-check-circle me-1"></i>Odohraných</div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="stat-card" style="border-top-color:var(--warning)">
            <div class="num" style="color:var(--warning)"><?= $s_cakaju ?></div>
            <div class="lbl"><i class="bi bi-hourglass me-1"></i>Čaká na hranie</div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="stat-card" style="border-top-color:var(--accent)">
            <div class="num" style="color:var(--accent)"><?= $s_hodiny ?>h</div>
            <div class="lbl"><i class="bi bi-clock me-1"></i>Hodín odohraných</div>
        </div>
    </div>
    <div class="col-6 col-md col-lg">
        <div class="stat-card" style="border-top-color:#6b7280">
            <div class="num" style="color:#6b7280"><?= $s_zanre ?></div>
            <div class="lbl"><i class="bi bi-tags me-1"></i>Žánrov</div>
        </div>
    </div>
</div>

<!-- ===== FILTER / HLADANIE ===== -->
<div class="form-card mb-4">
    <form method="GET" action="index.php">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Vyhľadať</label>
                <input type="text" name="hladaj" class="form-control"
                       placeholder="Názov alebo vývojár..."
                       value="<?= htmlspecialchars($hladaj) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Žáner</label>
                <select name="zanr" class="form-select">
                    <option value="0">– Všetky –</option>
                    <?php while ($z = mysqli_fetch_assoc($zanre_opt)): ?>
                        <option value="<?= $z['id'] ?>" <?= $zanr == $z['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($z['nazov']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Platforma</label>
                <input type="text" name="platforma" class="form-control"
                       placeholder="napr. PC"
                       value="<?= htmlspecialchars($platforma) ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Stav</label>
                <select name="stav" class="form-select">
                    <option value="">– Všetky –</option>
                    <option value="1" <?= $stav === '1' ? 'selected' : '' ?>>Odohraté</option>
                    <option value="0" <?= $stav === '0' ? 'selected' : '' ?>>Neodohraté</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search"></i>
                </button>
                <a href="index.php" class="btn btn-outline-secondary flex-grow-1">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- ===== HLAVICKA ZOZNAMU ===== -->
<?php $pocet = mysqli_num_rows($vysledky); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-semibold">
        <?= $pocet ?>
        <?= $pocet === 1 ? 'hra' : ($pocet < 5 ? 'hry' : 'hier') ?>
    </h5>
    <a href="pages/pridat.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Pridať hru
    </a>
</div>

<!-- ===== ZOZNAM HER ===== -->
<?php if ($pocet === 0): ?>
    <div class="text-center py-5 text-muted">
        <i class="bi bi-search" style="font-size:3rem;opacity:.4"></i>
        <p class="mt-3 mb-1">Žiadne hry sa nezhodujú s filtrom.</p>
        <a href="index.php" class="btn btn-sm btn-outline-secondary mt-1">Zobraziť všetky</a>
    </div>
<?php else: ?>
<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4">
    <?php while ($hra = mysqli_fetch_assoc($vysledky)): ?>
    <div class="col">
        <div class="game-card shadow-sm">

            <!-- Nahladovy obrazok / placeholder -->
            <div class="game-thumb <?= $hra['zahrana'] ? 'played' : '' ?>">
                <i class="bi bi-controller"></i>
                <?php if ($hra['platforma']): ?>
                    <span class="platform-tag"><?= htmlspecialchars($hra['platforma']) ?></span>
                <?php endif; ?>
            </div>

            <div class="game-body">
                <!-- Zanr badge -->
                <?php if ($hra['zanr_nazov']): ?>
                    <span class="badge-zanr"><?= htmlspecialchars($hra['zanr_nazov']) ?></span>
                <?php endif; ?>

                <!-- Nazov + vyvojar -->
                <div class="game-title"><?= htmlspecialchars($hra['nazov']) ?></div>
                <div class="text-muted" style="font-size:.8rem;">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($hra['vyvojar']) ?>
                    <?php if ($hra['rok_vydania']): ?>
                        &middot; <?= $hra['rok_vydania'] ?>
                    <?php endif; ?>
                </div>

                <!-- Cas hrania -->
                <?php if ($hra['cas_hrania']): ?>
                <div class="playtime mt-1">
                    <i class="bi bi-clock-fill me-1"></i><?= $hra['cas_hrania'] ?> hodín
                </div>
                <?php endif; ?>

                <!-- Hodnotenie -->
                <?php if ($hra['hodnotenie']): ?>
                <div class="stars mt-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="bi bi-star<?= $i <= $hra['hodnotenie'] ? '-fill' : '' ?> <?= $i > $hra['hodnotenie'] ? 'empty' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <!-- Akcie -->
                <div class="d-flex gap-1 mt-auto pt-2">
                    <a href="pages/detail.php?id=<?= $hra['id'] ?>"
                       class="btn btn-sm btn-outline-primary flex-grow-1"
                       title="Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="pages/upravit.php?id=<?= $hra['id'] ?>"
                       class="btn btn-sm btn-outline-secondary"
                       title="Upraviť">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="pages/vymazat.php?id=<?= $hra['id'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       title="Vymazať"
                       onclick="return confirm('Naozaj vymazať hru: <?= addslashes(htmlspecialchars($hra['nazov'])) ?>?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
