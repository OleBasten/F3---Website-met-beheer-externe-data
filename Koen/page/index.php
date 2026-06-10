<?php
require_once '../class/Database.php';
require_once '../class/Product.php';
require_once  '../page/games.php';


$db      = new Database();
$product = new Product($db->getConnection());

$id     = (int)($_POST['id'] ?? 0);
$name   = trim($_POST['name'] ?? '');
$price  = (float)($_POST['price'] ?? 0);
$action = $_POST['action'] ?? '';

if ($action === 'create' && $name && $price > 0) {
    $product->create($name, $price);
} elseif ($action === 'update' && $id && $name && $price > 0) {
    $product->update($id, $name, $price);
} elseif ($action === 'delete' && $id) {
    $product->delete($id);
}

$result = $product->getAll();
?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Product beheer</title>
        <link rel="stylesheet" href="../style/style.css">
    </head>
    <body>
    <div class="page-wrapper">

        <p class="page-title">Product beheer</p>
        <p class="page-sub">CRUD Dashboard</p>

        <div class="add-form">
            <strong>Nieuw product toevoegen</strong>
            <form method="post">
                <input type="text"   name="name"  placeholder="Productnaam" required>
                <input type="number" name="price" placeholder="Prijs (€)" step="0.01" min="0" required>
                <button name="action" value="create">+ Toevoegen</button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th style="width:60px">ID</th>
                    <th>Naam</th>
                    <th style="width:130px">Prijs (€)</th>
                    <th style="width:180px">Acties</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <td><span class="id-badge"><?= $row['id'] ?></span></td>
                            <td><input type="text"   name="name"  value="<?= htmlspecialchars($row['name']) ?>"></td>
                            <td><input type="number" name="price" value="<?= $row['price'] ?>" step="0.01" min="0"></td>
                            <td>
                                <div class="actions">
                                    <button name="action" value="update">Opslaan</button>
                                    <button name="action" value="delete"
                                            onclick="return confirm('Weet je het zeker?')">Verwijderen</button>
                                </div>
                            </td>
                        </form>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
    </body>
    </html>
<?php $db->close(); ?>