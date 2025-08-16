<?php
require_once '../includes/auth.php';
require_once '../includes/header.php';

// Handle complaint status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_complaint'])) {
    $complaint_id = $_POST['complaint_id'];
    $status = $_POST['status'];
    $resolution = $_POST['resolution'];
    
    try {
        $stmt = $pdo->prepare("
            UPDATE complaints 
            SET status = ?, resolution = ?, resolved_by = ?, updated_at = NOW() 
            WHERE complaint_id = ?
        ");
        $stmt->execute([$status, $resolution, $_SESSION['user_id'], $complaint_id]);
        $_SESSION['success'] = "Complaint updated successfully!";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating complaint: " . $e->getMessage();
    }
    
    header("Location: manage_complaints.php");
    exit();
}

// Get all complaints with user info
$stmt = $pdo->query("
    SELECT c.*, u.full_name as user_name, u.email as user_email, 
           a.full_name as resolved_by_name, o.order_id as order_number
    FROM complaints c
    JOIN users u ON c.user_id = u.user_id
    LEFT JOIN users a ON c.resolved_by = a.user_id
    LEFT JOIN orders o ON c.order_id = o.order_id
    ORDER BY c.created_at DESC
");
$complaints = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4">Complaint Management</h2>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="complaintsTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Complaint ID</th>
                                <th>User</th>
                                <th>Order #</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td><?php echo $complaint['complaint_id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($complaint['user_name']); ?><br>
                                    <small><?php echo htmlspecialchars($complaint['user_email']); ?></small>
                                </td>
                                <td>
                                    <?php if ($complaint['order_number']): ?>
                                        <a href="../orders/order_details.php?id=<?php echo $complaint['order_id']; ?>">
                                            #<?php echo $complaint['order_number']; ?>
                                        </a>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                <td>
                                    <span class="badge 
                                        <?php 
                                        switch($complaint['status']) {
                                            case 'open': echo 'bg-danger'; break;
                                            case 'in_progress': echo 'bg-warning'; break;
                                            case 'resolved': echo 'bg-success'; break;
                                            case 'rejected': echo 'bg-secondary'; break;
                                        }
                                        ?>
                                    ">
                                        <?php echo str_replace('_', ' ', ucfirst($complaint['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($complaint['created_at'])); ?></td>
                                <td>
                                    <a href="complaint_details.php?id=<?php echo $complaint['complaint_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>