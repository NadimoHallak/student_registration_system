<?php
session_start();
require 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// إعدادات التصفح بالصفحات
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// البحث
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = $search ? "WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search" : "";

// استعلام لجلب عدد السجلات
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM students $search_query");
if ($search) {
    $count_stmt->execute([':search' => "%$search%"]);
} else {
    $count_stmt->execute();
}
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $records_per_page);

// استعلام لجلب الطلاب مع البحث والتصفح
$query = "SELECT * FROM students $search_query ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <h2>Student Records</h2>

        <!-- شريط البحث -->

        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="d-flex">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, email, or phone" value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
            <a href="add_student.php" class="btn btn-success">Add Student</a>
        </div>


        <!-- جدول عرض الطلاب -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students): ?>
                    <?php foreach ($students as $index => $student): ?>
                        <tr>
                            <td><?= $index + 1 + $offset ?></td>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td><?= htmlspecialchars($student['phone']) ?></td>
                            <td><?= htmlspecialchars($student['address']) ?></td>
                            <td>
                                <a href="update_student.php?id=<?= $student['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_student.php?id=<?= $student['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- التصفح بالصفحات -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</body>

</html>