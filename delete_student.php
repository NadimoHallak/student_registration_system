<?php
session_start();
require 'config.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// التأكد من وجود ID الطالب
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // حذف الطالب من قاعدة البيانات
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    if ($stmt->execute([$id])) {
        header("Location: view_students.php");
        exit();
    } else {
        echo "Failed to delete student.";
    }
} else {
    header("Location: view_students.php");
    exit();
}
?>
