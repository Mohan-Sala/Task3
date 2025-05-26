<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'curd_app';
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$message = '';
$message_type = 'success';
if (isset($_POST['create'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sql = "INSERT INTO users (name, email) VALUES ('$name', '$email')";
    if ($conn->query($sql) === TRUE) {
        $message = "New user added successfully!";
        $message_type = 'success';
    } else {
        $message = "Error: " . $conn->error;
        $message_type = 'danger';
    }
}
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $sql = "UPDATE users SET name='$name', email='$email' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        $message = "User updated successfully!";
        $message_type = 'success';
    } else {
        $message = "Error: " . $conn->error;
        $message_type = 'danger';
    }
}
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        $message = "User deleted successfully!";
        $message_type = 'success';
    } else {
        $message = "Error: " . $conn->error;
        $message_type = 'danger';
    }
}
// SEARCH functionality
$search = '';
$where_clause = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause = "WHERE name LIKE '%$search%' OR email LIKE '%$search%'";
}
// PAGINATION setup
$records_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$count_sql = "SELECT COUNT(*) as total FROM users $where_clause";
$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
$sql = "SELECT * FROM users $where_clause ORDER BY id DESC LIMIT $records_per_page OFFSET $offset";
$result = $conn->query($sql);
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM users WHERE id=$edit_id";
    $edit_result = $conn->query($edit_sql);
    $edit_user = $edit_result->fetch_assoc();
}
$url_params = '';
if (!empty($search)) {
    $url_params = '&search=' . urlencode($search);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enhanced CRUD Application</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .search-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        .pagination {
            justify-content: center;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-center text-primary">
                    <i class="fas fa-users"></i> Enhanced CRUD Application
                </h1>
                <p class="text-center text-muted">Manage users with search and pagination</p>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $edit_user ? 'edit' : 'plus'; ?>"></i>
                            <?php echo $edit_user ? 'Edit User' : 'Add New User'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_user): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user"></i> Name
                                </label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['name']) : ''; ?>" 
                                       required placeholder="Enter full name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>" 
                                       required placeholder="Enter email address">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="<?php echo $edit_user ? 'update' : 'create'; ?>" 
                                        class="btn btn-gradient">
                                    <i class="fas fa-<?php echo $edit_user ? 'save' : 'plus'; ?>"></i>
                                    <?php echo $edit_user ? 'Update User' : 'Add User'; ?>
                                </button>
                                <?php if ($edit_user): ?>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> All Users 
                            <span class="badge bg-light text-dark"><?php echo $total_records; ?></span>
                        </h5>
                    </div>
                    <!-- SEARCH FORM -->
                    <div class="search-container">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" name="search" 
                                           value="<?php echo htmlspecialchars($search); ?>" 
                                           placeholder="Search by name or email...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <?php if (!empty($search)): ?>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th><i class="fas fa-hashtag"></i> ID</th>
                                            <th><i class="fas fa-user"></i> Name</th>
                                            <th><i class="fas fa-envelope"></i> Email</th>
                                            <th><i class="fas fa-calendar"></i> Created At</th>
                                            <th><i class="fas fa-cogs"></i> Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo $row['id']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></td>
                                                <td>
                                                    <a href="?edit=<?php echo $row['id']; ?><?php echo $url_params; ?>" 
                                                       class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $row['id']; ?><?php echo $url_params; ?>&page=<?php echo $page; ?>" 
                                                       class="btn btn-sm btn-outline-danger" title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($row['name']); ?>?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- PAGINATION -->
                            <?php if ($total_pages > 1): ?>
                                <div class="card-footer">
                                    <nav aria-label="Users pagination">
                                        <ul class="pagination mb-0">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($page-1); ?><?php echo $url_params; ?>">
                                                        <i class="fas fa-chevron-left"></i> Previous
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">
                                                        <i class="fas fa-chevron-left"></i> Previous
                                                    </span>
                                                </li>
                                            <?php endif; ?>
                                            <?php
                                            $start_page = max(1, $page - 2);
                                            $end_page = min($total_pages, $page + 2);
                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $url_params; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            <?php if ($page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?page=<?php echo ($page+1); ?><?php echo $url_params; ?>">
                                                        Next <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php else: ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">
                                                        Next <i class="fas fa-chevron-right"></i>
                                                    </span>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            Showing <?php echo (($page-1) * $records_per_page + 1); ?> to 
                                            <?php echo min($page * $records_per_page, $total_records); ?> of 
                                            <?php echo $total_records; ?> entries
                                            <?php if (!empty($search)): ?>
                                                (filtered from total entries)
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">
                                    <?php echo !empty($search) ? 'No users found matching your search.' : 'No users found.'; ?>
                                </h5>
                                <?php if (!empty($search)): ?>
                                    <a href="index.php" class="btn btn-outline-primary">
                                        <i class="fas fa-list"></i> Show All Users
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>