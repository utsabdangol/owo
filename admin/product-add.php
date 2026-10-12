<?php
/**
 * OWO Admin — Add New Product
 */
$pageTitle  = 'Add Product';
$activePage = 'products';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$errors = [];
$values = ['name'=>'','description'=>'','price'=>'','stock'=>'','category'=>'','sizes'=>'S,M,L,XL'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name']        = trim($_POST['name']        ?? '');
    $values['description'] = trim($_POST['description'] ?? '');
    $values['price']       = trim($_POST['price']       ?? '');
    $values['stock']       = trim($_POST['stock']       ?? '');
    $values['category']    = trim($_POST['category']    ?? '');
    $values['sizes']       = trim($_POST['sizes']       ?? 'S,M,L,XL');

    if (!$values['name'])              $errors[] = 'Product name is required.';
    if (!is_numeric($values['price']) || $values['price'] < 0) $errors[] = 'Valid price is required.';
    if (!is_numeric($values['stock']) || $values['stock'] < 0) $errors[] = 'Valid stock quantity is required.';
    if (!$values['category'])          $errors[] = 'Category is required.';

    // Handle image upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $imagePath = uploadProductImage($_FILES['image']);
        if (!$imagePath) $errors[] = 'Image upload failed. Use JPG, PNG, or WebP under 5MB.';
    }

    if (empty($errors)) {
        $db = getDB();
        $db->prepare("
            INSERT INTO products (name, description, price, stock, category, sizes, image_path)
            VALUES (:name, :desc, :price, :stock, :cat, :sizes, :img)
        ")->execute([
            ':name'  => $values['name'],
            ':desc'  => $values['description'],
            ':price' => (float)$values['price'],
            ':stock' => (int)$values['stock'],
            ':cat'   => strtolower($values['category']),
            ':sizes' => $values['sizes'],
            ':img'   => $imagePath,
        ]);
        setFlash('success', 'Product "' . $values['name'] . '" added successfully.');
        redirect(APP_URL . '/admin/products.php');
    }
}

require_once __DIR__ . '/partials/layout-header.php';
?>

<nav class="breadcrumb">
  <a href="<?= APP_URL ?>/admin/products.php">Products</a>
  <span class="breadcrumb__sep">›</span>
  <span>Add New</span>
</nav>

<?php if (!empty($errors)): ?>
  <div class="flash flash--error">
    <?php foreach ($errors as $e): ?><p>• <?= e($e) ?></p><?php endforeach; ?>
  </div>
<?php endif; ?>

<div style="max-width:740px;">
  <div class="form-card">
    <h2>Add New Product</h2>
    <form method="POST" enctype="multipart/form-data" id="addProductForm">

      <div class="form-group">
        <label class="form-label" for="prodName">Product Name *</label>
        <input class="form-input" type="text" name="name" id="prodName" value="<?= e($values['name']) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="prodDesc">Description</label>
        <textarea class="form-textarea" name="description" id="prodDesc"><?= e($values['description']) ?></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="prodPrice">Price (NPR) *</label>
          <input class="form-input" type="number" name="price" id="prodPrice" step="0.01" min="0" value="<?= e($values['price']) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="prodStock">Stock Qty *</label>
          <input class="form-input" type="number" name="stock" id="prodStock" min="0" value="<?= e($values['stock']) ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="prodCategory">Category *</label>
          <input class="form-input" type="text" name="category" id="prodCategory" value="<?= e($values['category']) ?>" placeholder="e.g. tees, hoodies, bottoms" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="prodSizes">Available Sizes</label>
          <input class="form-input" type="text" name="sizes" id="prodSizes" value="<?= e($values['sizes']) ?>" placeholder="S,M,L,XL">
          <span class="form-hint">Comma-separated. e.g. S,M,L,XL or ONE SIZE</span>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="imageInput">Product Image</label>
        <input class="form-input" type="file" name="image" id="imageInput" accept="image/jpeg,image/png,image/webp">
        <span class="form-hint">JPG, PNG, or WebP · Max 5MB · Recommended 4:5 ratio</span>
        <img id="imgPreview" src="" alt="Preview">
      </div>

      <div style="display:flex;gap:12px;margin-top:8px;">
        <button type="submit" class="btn btn-primary" id="submitAddProduct">Add Product</button>
        <a href="<?= APP_URL ?>/admin/products.php" class="btn btn-outline">Cancel</a>
      </div>

    </form>
  </div>
</div>

<?php require_once __DIR__ . '/partials/layout-footer.php'; ?>
