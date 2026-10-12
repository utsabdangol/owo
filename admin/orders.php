<?php
/**
 * OWO Admin — Orders List
 */
$pageTitle  = 'Orders';
$activePage = 'orders';

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
requireAdmin();

$db = getDB();

$statusFilter = $_GET['status'] ?? '';
$search       = trim($_GET['q'] ?? '');

$where  = 'WHERE 1=1';
$params = [];

if ($statusFilter && in_array($statusFilter, ['pending','paid','shipped','delivered','cancelled'])) {
    $where .= ' AND o.status = :status';
    $params[':status'] = $statusFilter;
}
if ($search) {
    $where .= ' AND (o.guest_name LIKE :q OR o.guest_email LIKE :q2 OR u.name LIKE :q3)';
    $params[':q']  = '%' . $search . '%';
    $params[':q2'] = '%' . $search . '%';
    $params[':q3'] = '%' . $search . '%';
}

$total = $db->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN users u ON u.id = o.user_id $where");
$total->execute($params);
$total = (int) $total->fetchColumn();

$pag  = paginate($total, 20);
$stmt = $db->prepare("
    SELECT o.*, COALESCE(u.name, o.guest_name) AS buyer_name,
           COALESCE(u.email, o.guest_email) AS buyer_email
    FROM orders o
    LEFT JOIN users u ON u.id = o.user_id
    $where
    ORDER BY o.created_at DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $pag['per_page'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'],   PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll();

require_once __DIR__ . '/partials/layout-header.php';
?>

<div class="admin-table-wrap">
  <div class="admin-table-header">
    <h2>Orders (<?= $total ?>)</h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <!-- Status filter tabs -->
      <?php foreach ([''=>'All','pending'=>'Pending','paid'=>'Paid','shipped'=>'Shipped','delivered'=>'Delivered','cancelled'=>'Cancelled'] as $val => $label): ?>
        <a
          href="?status=<?= e($val) ?><?= $search ? '&q='.urlencode($search) : '' ?>"
          class="btn btn-sm <?= $statusFilter === $val ? 'btn-primary' : 'btn-outline' ?>"
        ><?= $label ?></a>
      <?php endforeach; ?>
      <form method="GET" class="search-bar" style="margin-left:auto;">
        <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
        <input class="search-input" type="search" name="q" placeholder="Search buyer…" value="<?= e($search) ?>">
        <button class="btn btn-outline btn-sm" type="submit">Search</button>
      </form>
    </div>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-state"><h3>No orders found.</h3></div>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Buyer</th>
            <th>Email</th>
            <th>Total</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><strong>#OWO-<?= str_pad($order['id'],4,'0',STR_PAD_LEFT) ?></strong></td>
              <td><?= e($order['buyer_name'] ?? '—') ?></td>
              <td><?= e($order['buyer_email'] ?? '—') ?></td>
              <td><?= formatPrice((float)$order['total_amount']) ?></td>
              <td><span class="status-badge status-badge--<?= e($order['status']) ?>"><?= ucfirst(e($order['status'])) ?></span></td>
              <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
              <td>
                <a href="<?= APP_URL ?>/admin/order-detail.php?id=<?= $order['id'] ?>" class="btn btn-outline btn-sm" id="view-order-<?= $order['id'] ?>">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?= paginationLinks($pag, APP_URL . '/admin/orders.php?' . http_build_query(array_filter(['status'=>$statusFilter,'q'=>$search]))) ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/layout-footer.php'; ?>
