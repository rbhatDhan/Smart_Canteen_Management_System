<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Handle Save Update
if (isset($_POST['save_update'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);

    if ($id != '' && $name != '' && $price != '') {
        $stmt = $conn->prepare("UPDATE menu SET name=?, price=? WHERE id=?");
        $stmt->bind_param("sdi", $name, $price, $id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: update_menu.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM menu WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: update_menu.php");
    exit;
}

// Handle Add New Menu Item
if (isset($_POST['add_menu'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $menu_day = $_POST['menu_day'];
    $duplicate = false;

    if ($name != '' && $price != '' && $menu_day != '') {
        // Check for duplicate (case insensitive)
        $check = $conn->prepare("SELECT * FROM menu WHERE LOWER(menu_day) = LOWER(?) AND LOWER(name) = LOWER(?)");
        $check->bind_param("ss", $menu_day, $name);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $duplicate = true;
        }
        $check->close();

        if ($duplicate) {
            // Show alert and redirect back
            echo '<!DOCTYPE html>
            <html>
            <head>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: "warning",
                        title: "Duplicate Dish!",
                        text: "This dish already exists for ' . $menu_day . '!",
                        confirmButtonColor: "#0d9488"
                    }).then(() => {
                        window.location.href = "update_menu.php";
                    });
                </script>
            </body>
            </html>';
            exit();
        } else {
            // Insert new dish
            $stmt = $conn->prepare("INSERT INTO menu (name, price, menu_day) VALUES (?, ?, ?)");
            $stmt->bind_param("sds", $name, $price, $menu_day);
            
            if ($stmt->execute()) {
                // Show success alert
                echo '<!DOCTYPE html>
                <html>
                <head>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                </head>
                <body>
                    <script>
                        Swal.fire({
                            icon: "success",
                            title: "Success!",
                            text: "Dish added successfully!",
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = "update_menu.php";
                        });
                    </script>
                </body>
                </html>';
                exit();
            }
            $stmt->close();
        }
    }
}

// Fetch menu items
$sql = "SELECT * FROM menu 
        ORDER BY FIELD(menu_day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), name";
$result = $conn->query($sql);
$menu = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $menu[$row['menu_day']][] = $row;
    }
}
$conn->close();

$edit_id = isset($_GET['edit_id']) ? $_GET['edit_id'] : null;
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Menu - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #14b8a6;
            --primary-soft: #ccfbf1;
            --dark: #115e59;
            --darker: #134e4a;
            --gray: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            padding: 2rem;
            position: relative;
            background: #f8fafc;
        }

        /* Random Rectangle Background */
        .rectangle-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .rect {
            position: absolute;
            background: var(--primary);
            opacity: 0.10;
            border-radius: 30px;
            box-shadow: 0 0 30px rgba(13,148,136,0.1);
        }

        .rect-1 {
            top: 5%;
            left: 2%;
            width: 350px;
            height: 180px;
            transform: rotate(12deg);
            animation: floatRect1 22s ease-in-out infinite;
        }

        .rect-2 {
            top: 55%;
            right: 3%;
            width: 450px;
            height: 220px;
            transform: rotate(-8deg);
            animation: floatRect2 26s ease-in-out infinite reverse;
        }

        .rect-3 {
            bottom: 10%;
            left: 10%;
            width: 300px;
            height: 400px;
            transform: rotate(20deg);
            animation: floatRect3 24s ease-in-out infinite;
        }

        .rect-4 {
            top: 25%;
            right: 15%;
            width: 400px;
            height: 200px;
            transform: rotate(-15deg);
            animation: floatRect4 28s ease-in-out infinite;
        }

        .rect-5 {
            bottom: 30%;
            left: 35%;
            width: 250px;
            height: 320px;
            transform: rotate(35deg);
            animation: floatRect5 25s ease-in-out infinite 1s;
        }

        .rect-6 {
            top: 70%;
            right: 25%;
            width: 380px;
            height: 190px;
            transform: rotate(-25deg);
            animation: floatRect6 27s ease-in-out infinite 0.5s;
        }

        .rect-7 {
            top: 12%;
            left: 25%;
            width: 320px;
            height: 250px;
            transform: rotate(8deg);
            animation: floatRect7 23s ease-in-out infinite 1.5s;
        }

        .rect-8 {
            bottom: 15%;
            right: 35%;
            width: 420px;
            height: 210px;
            transform: rotate(-12deg);
            animation: floatRect8 29s ease-in-out infinite 2s;
        }

        @keyframes floatRect1 {
            0%, 100% { transform: rotate(12deg) translate(0, 0); }
            50% { transform: rotate(12deg) translate(45px, -35px); }
        }

        @keyframes floatRect2 {
            0%, 100% { transform: rotate(-8deg) translate(0, 0); }
            50% { transform: rotate(-8deg) translate(-40px, 45px); }
        }

        @keyframes floatRect3 {
            0%, 100% { transform: rotate(20deg) translate(0, 0) scale(1); }
            50% { transform: rotate(20deg) translate(35px, 35px) scale(1.08); }
        }

        @keyframes floatRect4 {
            0%, 100% { transform: rotate(-15deg) translate(0, 0); }
            50% { transform: rotate(-15deg) translate(-45px, -25px); }
        }

        @keyframes floatRect5 {
            0%, 100% { transform: rotate(35deg) translate(0, 0); }
            50% { transform: rotate(35deg) translate(25px, -45px); }
        }

        @keyframes floatRect6 {
            0%, 100% { transform: rotate(-25deg) translate(0, 0); }
            50% { transform: rotate(-25deg) translate(-30px, 35px); }
        }

        @keyframes floatRect7 {
            0%, 100% { transform: rotate(8deg) translate(0, 0); }
            50% { transform: rotate(8deg) translate(35px, -25px); }
        }

        @keyframes floatRect8 {
            0%, 100% { transform: rotate(-12deg) translate(0, 0); }
            50% { transform: rotate(-12deg) translate(-45px, 25px); }
        }

        .menu-page {
            padding: 2rem;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Back Button */
        .back-to-dashboard {
            margin-bottom: 1.5rem;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid var(--primary-soft);
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
        }

        .page-header {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border: 1px solid rgba(13,148,136,0.1);
        }

        .page-header h1 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--gray);
        }

        .day-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border: 1px solid rgba(13,148,136,0.1);
        }

        .day-tab {
            padding: 0.5rem 1.5rem;
            background: #f1f5f9;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .day-tab:hover,
        .day-tab.active {
            background: var(--primary);
            color: white;
        }

        .day-section {
            display: none;
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(13,148,136,0.1);
        }

        .day-section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-soft);
        }

        .day-header h2 {
            font-size: 1.5rem;
            color: var(--dark);
        }

        .item-count {
            background: var(--primary-soft);
            color: var(--primary);
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .menu-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .menu-table th {
            text-align: left;
            padding: 1rem;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-weight: 600;
        }

        .menu-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .menu-table tr:hover td {
            background: #f8fafc;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-icon {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-edit {
            background: #3b82f6;
        }

        .btn-edit:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #ef4444;
        }

        .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-save {
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            margin-right: 0.5rem;
            transition: all 0.3s;
        }

        .btn-save:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .add-form {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            border: 1px solid var(--primary-soft);
        }

        .add-form input {
            flex: 1;
            min-width: 200px;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .add-form input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13,148,136,0.1);
        }

        .btn-add {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--primary-soft);
            margin-bottom: 1rem;
        }

        .cancel-link {
            padding: 0.5rem 1rem;
            border: 2px solid var(--primary);
            color: var(--primary);
            text-decoration: none;
            border-radius: 0.5rem;
            margin-left: 0.5rem;
            transition: all 0.3s;
        }

        .cancel-link:hover {
            background: var(--primary);
            color: white;
        }

        @media (max-width: 768px) {
            .add-form {
                flex-direction: column;
            }
            .add-form input {
                width: 100%;
            }
            .btn-add {
                width: 100%;
                justify-content: center;
            }
            .day-tabs {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Random Rectangle Background -->
    <div class="rectangle-bg">
        <div class="rect rect-1"></div>
        <div class="rect rect-2"></div>
        <div class="rect rect-3"></div>
        <div class="rect rect-4"></div>
        <div class="rect rect-5"></div>
        <div class="rect rect-6"></div>
        <div class="rect rect-7"></div>
        <div class="rect rect-8"></div>
    </div>

    <div class="menu-page">
        <div class="menu-container">
            <!-- Back to Dashboard Button -->
            <div class="back-to-dashboard">
                <a href="admin_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-utensils" style="color: var(--primary);"></i> Update Weekly Menu</h1>
                <p>Add, edit, or remove dishes for each day</p>
            </div>

            <!-- Day Tabs -->
            <div class="day-tabs">
                <?php foreach($days as $index => $day): ?>
                    <div class="day-tab <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showDay('<?php echo $day; ?>')">
                        <?php echo $day; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Menu Sections -->
            <?php foreach($days as $day): ?>
                <div id="day-<?php echo $day; ?>" class="day-section <?php echo $day === 'Monday' ? 'active' : ''; ?>">
                    <div class="day-header">
                        <h2><i class="fas fa-utensils" style="color: var(--primary);"></i> <?php echo $day; ?></h2>
                        <span class="item-count"><?php echo isset($menu[$day]) ? count($menu[$day]) : 0; ?> items</span>
                    </div>

                    <?php if(!empty($menu[$day])): ?>
                        <div class="table-responsive">
                            <table class="menu-table">
                                <thead>
                                    <tr>
                                        <th>Dish Name</th>
                                        <th>Price (₹)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($menu[$day] as $dish): ?>
                                        <tr>
                                            <?php if($edit_id == $dish['id']): ?>
                                                <form method="POST">
                                                    <td>
                                                        <input type="text" name="name" value="<?php echo htmlspecialchars($dish['name']); ?>" style="padding: 0.5rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; width: 100%;" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="price" value="<?php echo $dish['price']; ?>" step="0.01" style="padding: 0.5rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; width: 100%;" required>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="id" value="<?php echo $dish['id']; ?>">
                                                        <button type="submit" name="save_update" class="btn-save">
                                                            <i class="fas fa-save"></i> Save
                                                        </button>
                                                        <a href="update_menu.php" class="cancel-link">Cancel</a>
                                                    </td>
                                                </form>
                                            <?php else: ?>
                                                <td><strong><?php echo htmlspecialchars($dish['name']); ?></strong></td>
                                                <td>₹<?php echo number_format($dish['price'], 2); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="update_menu.php?edit_id=<?php echo $dish['id']; ?>" class="btn-icon btn-edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" 
                                                           class="btn-icon btn-delete"
                                                           onclick="confirmDelete(<?php echo $dish['id']; ?>, '<?php echo htmlspecialchars($dish['name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-utensil-spoon"></i>
                            <p>No dishes added for <?php echo $day; ?> yet.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Add New Dish Form -->
                    <form method="POST" class="add-form">
                        <input type="text" name="name" placeholder="Dish Name" required>
                        <input type="number" name="price" placeholder="Price (₹)" step="0.01" required>
                        <input type="hidden" name="menu_day" value="<?php echo $day; ?>">
                        <button type="submit" name="add_menu" class="btn-add">
                            <i class="fas fa-plus-circle"></i> Add to <?php echo $day; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function showDay(day) {
            document.querySelectorAll('.day-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.day-tab').forEach(t => t.classList.remove('active'));
            document.getElementById('day-' + day).classList.add('active');
            event.target.classList.add('active');
        }

        function confirmDelete(id, dishName) {
            Swal.fire({
                title: 'Delete Dish?',
                text: `Are you sure you want to delete "${dishName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'update_menu.php?delete_id=' + id;
                }
            });
        }
    </script>
</body>
</html>