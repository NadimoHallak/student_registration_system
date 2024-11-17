<?php
session_start();
require 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// الحصول على بيانات الطالب بناءً على ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        echo "Student not found!";
        exit();
    }
} else {
    header("Location: view_students.php");
    exit();
}

// تحديث بيانات الطالب عند إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $profile_picture = $student['profile_picture'];

    // رفع صورة جديدة إذا تم اختيارها
    if (!empty($_FILES['profile_picture']['name'])) {
        $upload_dir = 'uploads/';
        $profile_picture = $upload_dir . basename($_FILES['profile_picture']['name']);
        if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture)) {
            $error = "Failed to upload new profile picture.";
        }
    }

    // تحديث البيانات في قاعدة البيانات
    if (!isset($error)) {
        $stmt = $pdo->prepare("UPDATE students SET name = ?, email = ?, phone = ?, address = ?, profile_picture = ? WHERE id = ?");
        if ($stmt->execute([$name, $email, $phone, $address, $profile_picture, $id])) {
            header("Location: view_students.php");
            exit();
        } else {
            $error = "Failed to update student.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h2>Update Student</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($student['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($student['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea name="address" id="address" class="form-control" required><?= htmlspecialchars($student['address']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                <?php if ($student['profile_picture']): ?>
                    <img src="<?= $student['profile_picture'] ?>" alt="Profile Picture" class="img-thumbnail mt-2" width="100">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Student</button>
        </form>
    </div>
</body>
</html>
