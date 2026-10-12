<?php
/**
 * OWO Admin — Edit Product
 */
$pageTitle  = 'Edit Product';
$activePage = 'products';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$db = getDB();
$id = (int) ($_GET['id'] ?? 0);
if (!$id) { redirect(APP_URL . '/admin/products.php'); }

$product = $db->prepare("SELECT * FROM products WHERE id = :id AND is_deleted = 0");
$product->execute([':id' => $id]);
$product = $product->fetch();
if (!$product) { setFlash('error','Product not found.'); redirect(APP_URL . '/admin/products.php'); }

$errors = [];
$values = $product; // pre-fill with current values

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name']        = trim($_POST['name']        ?? '');
    $values['description'] = trim($_POST['description'] ?? '');
    $values['price']       = trim($_POST['price']       ?? '');
    $values['stock']       = trim($_POST['stock']       ?? '');
    $values['category']    = trim($_POST['category']    ?? '');
    $values['sizes']       = trim($_POST['sizes']       ?? '');

    if (!$values['name'])              $errors[] = 'Product name is required.';
    if (!is_numeric($values['price'])) $errors[] = 'Valid price is required.';
    if (!is_numeric($values['stock'])) $errors[] = 'Valid stock is required.';

    // Handle new image upload
    $imagePath = $product['image_path']; // keep existing by default
    if (!empty($_FILES['image']['name'])) {
        $newImg = uploadProductImage($_FILES['image']);
        if ($newImg) {
            // Delete old image
            if ($imagePath && file_exists(UPLOAD_DIR . $imagePath)) {
                @unlink(UPLOAD_DIR . $imagePath);
            }
            $imagePath = $newImg;
        } else {
            $errors[] = 'Image upload failed. Use JPG, PNG, or WebP under 5MB.';
        }
    }

    if (empty($errors)) {
        $db->prepare("
            UPDATE products
            SET name=:name, description=:desc, price=:price, stock=:stock,
                category=:cat, sizes=:sizes, image_path=:img
            WHERE id=:id
        ")->execute([
            ':name'  => $values['name'],
            ':desc'  => $values['description'],
            ':price' => (float)$values['price'],
            ':stock' => (int)$values['stock'],
            ':cat'   => strtolower($values['category']),
            ':sizes' => $values['sizes'],
            ':img'   => $imagePath,
            ':id'    => $id,
        ]);
        setFlash('success', 'Product updated.');
        redirect(APP_URL . '/admin/products.php');
    }
}

require_once __DIR__ . '/partials/layout-header.php';
?>

<nav class="breadcrumb">
  <a href="<?= APP_URL ?>/admin/products.php">Products</a>
  <span class="breadcrumb__sep">›</span>
  <span>Edit: <?= e($product['name']) ?></span>
</nav>

<?php if (!empty($errors)): ?>
  <div class="flash flash--error">
    <?php foreach ($errors as $err): ?><p>• <?= e($err) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<div style="max-width:740px;">
  <div class="form-card">
    <h2>Edit Product</h2>
    <form method="POST" enctype="multipart/form-data" id="editProductForm">

      <div class="form-group">
        <label class="form-label" for="editName">Product Name *</label>
        <input class="form-input" type="text" name="name" id="editName" value="<?= e($values['name']) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="editDesc">Description</label>
        <textarea class="form-textarea" name="description" id="editDesc"><?= e($values['description']) ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="editPrice">Price (NPR) *</label>
          <input class="form-input" type="number" name="price" id="editPrice" step="0.01" min="0" value="<?= e($values['price']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="editStock">Stock Qty *</label>
          <input class="form-input" type="number" name="stock" id="editStock" min="0" value="<?= e($values['stock']) ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="editCategory">Category *</label>
          <input class="form-input" type="text" name="category" id="editCategory" value="<?= e($values['category']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="editSizes">Sizes</label>
          <input class="form-input" type="text" name="sizes" id="editSizes" value="<?= e($values['sizes']) ?>">
        </div>
      </div>

      <!-- Current Image -->
      <div class="form-group">
        <label class="form-label">Current Image</label>
        <img
          src="<?= e(productImageUrl($product['image_path'])) ?>"
          alt="Current product image"
          style="width:100px;height:125px;object-fit:cover;border-radius:8px;border:1px solid var(--grey-200);"
        >
      </div>

      <div class="form-group">
        <label class="form-label" for="imageInput">Replace Image (optional)</label>
        <input class="form-input" type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp">
        <img id="imgPreview" src="" alt="New image preview">
      </div>

      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn btn-primary" id="saveEditBtn">Save Changes</button>
        <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline">Cancel</a>
        <a href="<?= APP_URL ?>/product.php?id=<?= $id ?>" target="_blank" class="btn btn-outline" style="margin-left:auto;">View on Site ↗</a>
      </div>

    </form>
  </div>
</div>

<?php require_once __DIR__ . '/partials/layout-footer.php'; ?>
