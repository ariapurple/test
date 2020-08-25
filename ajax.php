<?php
$GLOBALS['con'] = mysqli_connect("database", "root", "", "test");

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
} else {
    $role_id = $_POST['role_id'];
    $lesson_id = $_POST['lesson_id'];
    
    $query = "SELECT id from roles_has_lessons where role_id = $role_id and lesson_id = $lesson_id";
    
    $result = mysqli_query($GLOBALS['con'], $query);
    
    $result = mysqli_fetch_assoc($result);
    
    if ($result !== NULL) {
        // Delete
        $query = "DELETE FROM roles_has_lessons where role_id = $role_id and lesson_id = $lesson_id";

        $result = mysqli_query($GLOBALS['con'], $query);
        
        echo 1;
    }else{
        // Insert
        $query = "INSERT INTO roles_has_lessons (lesson_id, role_id) VALUES ($lesson_id, $role_id);";
    
        $result = mysqli_query($GLOBALS['con'], $query);
    
        echo 2;
    }
}
?>