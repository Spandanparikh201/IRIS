<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit(0);
}
include 'db_connect.php';
include 'rbac_helper.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management - I.R.I.S</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100vh; background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 30px 0; box-shadow: 5px 0 20px rgba(0,0,0,0.1); z-index: 1000; transition: transform 0.3s ease; }
        .sidebar.collapsed { transform: translateX(-220px); width: 60px; }
        .sidebar.collapsed .logo h1, .sidebar.collapsed .logo p, .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 15px; }
        .logo { text-align: center; padding: 0 30px 30px; border-bottom: 1px solid rgba(0,0,0,0.1); margin-bottom: 30px; }
        .logo h1 { font-size: 2rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; margin-bottom: 5px; }
        .logo p { color: #666; font-size: 0.9rem; }
        .nav-menu { list-style: none; padding: 0 20px; }
        .nav-item { margin-bottom: 10px; }
        .nav-link { display: flex; align-items: center; padding: 15px 20px; color: #555; text-decoration: none; border-radius: 15px; transition: all 0.3s ease; font-weight: 500; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateX(5px); }
        .nav-link i { margin-right: 12px; width: 20px; min-width: 20px; }
        .nav-link span { transition: opacity 0.3s ease; }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 60px; }
        .toggle-btn { position: fixed; top: 20px; left: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 1001; transition: all 0.3s ease; }
        .toggle-btn:hover { transform: scale(1.1); }
        .header { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); padding: 25px 30px; border-radius: 20px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .header-title h2 { color: #333; font-size: 2rem; margin-bottom: 5px; }
        .header-title p { color: #666; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-avatar { width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem; }
        .logout-btn { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 500; transition: all 0.3s ease; text-decoration: none; }
        .logout-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,107,107,0.4); }
        .card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border-radius: 20px; padding: 30px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.3); }
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 30px; }
        .nav-tabs .nav-link { background: rgba(255,255,255,0.7); border: 2px solid transparent; border-radius: 12px; padding: 12px 24px; color: #4a5568; text-decoration: none; font-weight: 500; transition: all 0.3s ease; }
        .nav-tabs .nav-link.active, .nav-tabs .nav-link:hover { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; transform: translateY(-2px); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .form-control { width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 1rem; transition: all 0.3s ease; background: white; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .btn { padding: 12px 24px; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102,126,234,0.3); }
        .btn-success { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); color: white; }
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(72,187,120,0.3); }
        .table-container { overflow-x: auto; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; background: white; }
        th { background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%); color: white; padding: 20px 16px; text-align: left; font-weight: 600; font-size: 0.9rem; }
        td { padding: 16px; border-bottom: 1px solid #e2e8f0; }
        tr:hover td { background-color: #f7fafc; }
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-danger { background: #fed7d7; color: #742a2a; }
        .row { display: flex; gap: 20px; margin-bottom: 20px; }
        .col-md-6 { flex: 1; }
        .col-md-4 { flex: 1; }
        @media (max-width: 1024px) { .sidebar { transform: translateX(-100%); } .sidebar.mobile-open { transform: translateX(0); } .main-content { margin-left: 0; } .header { flex-direction: column; gap: 20px; text-align: center; } .nav-tabs { flex-wrap: wrap; } }
    </style>
</head>
<body>
    <button class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    
    <div class="sidebar" id="sidebar">
        <div class="logo"><h1>I.R.I.S</h1><p>Dashboard</p></div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="nav-link active"><i class="fas fa-chart-line"></i><span>Dashboard</span></a></li>
            <li class="nav-item"><a href="add_student.php" class="nav-link"><i class="fas fa-users"></i><span>Students</span></a></li>
            <li class="nav-item"><a href="attendance.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>Attendance</span></a></li>
            <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-pie"></i><span>Reports</span></a></li>
            <li class="nav-item"><a href="library.php" class="nav-link"><i class="fas fa-book"></i><span>Library</span></a></li>
            <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i><span>Settings</span></a></li>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
            <li class="nav-item"><a href="manage_users.php" class="nav-link"><i class="fas fa-users-cog"></i><span>Manage Users</span></a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-title">
                <h2>📚 Library Management</h2>
                <p>Manage books, issues, and returns</p>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['user'], 0, 1)) ?></div>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="card">
            <div class="nav-tabs">
                <a href="#" class="nav-link active" onclick="showTab('books')"><i class="fas fa-book"></i> Books</a>
                <a href="#" class="nav-link" onclick="showTab('issue')"><i class="fas fa-share"></i> Issue Book</a>
                <a href="#" class="nav-link" onclick="showTab('return')"><i class="fas fa-undo"></i> Return Book</a>
                <?php if ($_SESSION['user_role'] === 'librarian'): ?>
                <a href="#" class="nav-link" onclick="showTab('add')"><i class="fas fa-plus"></i> Add Book</a>
                <?php endif; ?>
            </div>

            <!-- Books List -->
            <div id="books" class="tab-content active">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-book"></i> Available Books</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Available/Total</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT * FROM books ORDER BY title");
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>{$row['title']}</td>
                                    <td>{$row['author']}</td>
                                    <td>{$row['category']}</td>
                                    <td><span class='badge badge-" . ($row['available_copies'] > 0 ? 'success' : 'danger') . "'>{$row['available_copies']}/{$row['total_copies']}</span></td>
                                    <td>{$row['location']}</td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Issue Book -->
            <div id="issue" class="tab-content">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-share"></i> Issue Book</h3>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Book</label>
                                <select name="book_id" class="form-control" required>
                                    <option value="">Select Book</option>
                                    <?php
                                    $result = $conn->query("SELECT id, title, author, available_copies FROM books WHERE available_copies > 0");
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['id']}'>{$row['title']} by {$row['author']} (Available: {$row['available_copies']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Student Name</label>
                                <input type="text" name="student_name" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" class="form-control" required>
                    </div>
                    <button type="submit" name="issue_book" class="btn btn-primary"><i class="fas fa-check"></i> Issue Book</button>
                </form>
            </div>

            <!-- Return Book -->
            <div id="return" class="tab-content">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-undo"></i> Return Book</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Select Issued Book</label>
                        <select name="transaction_id" class="form-control" required>
                            <option value="">Select Book to Return</option>
                            <?php
                            $result = $conn->query("SELECT bt.id, b.title, bt.student_name, bt.issue_date, bt.due_date 
                                                   FROM book_transactions bt 
                                                   JOIN books b ON bt.book_id = b.id 
                                                   WHERE bt.status = 'issued'");
                            while ($row = $result->fetch_assoc()) {
                                $overdue = date('Y-m-d') > $row['due_date'] ? ' (OVERDUE)' : '';
                                echo "<option value='{$row['id']}'>{$row['title']} - {$row['student_name']} (Due: {$row['due_date']}){$overdue}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="return_book" class="btn btn-success"><i class="fas fa-check"></i> Return Book</button>
                </form>

                <h3 style="margin-top: 30px; margin-bottom: 20px;"><i class="fas fa-list"></i> Currently Issued Books</h3>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Student</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $conn->query("SELECT b.title, bt.student_name, bt.issue_date, bt.due_date 
                                                   FROM book_transactions bt 
                                                   JOIN books b ON bt.book_id = b.id 
                                                   WHERE bt.status = 'issued'");
                            while ($row = $result->fetch_assoc()) {
                                $status = date('Y-m-d') > $row['due_date'] ? 'Overdue' : 'Active';
                                $statusClass = $status == 'Overdue' ? 'danger' : 'success';
                                echo "<tr>
                                    <td>{$row['title']}</td>
                                    <td>{$row['student_name']}</td>
                                    <td>{$row['issue_date']}</td>
                                    <td>{$row['due_date']}</td>
                                    <td><span class='badge badge-{$statusClass}'>{$status}</span></td>
                                </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add Book -->
            <?php if ($_SESSION['user_role'] === 'librarian'): ?>
            <div id="add" class="tab-content">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-plus"></i> Add New Book</h3>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ISBN</label>
                                <input type="text" name="isbn" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Author</label>
                                <input type="text" name="author" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Publisher</label>
                                <input type="text" name="publisher" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Year</label>
                                <input type="number" name="year" class="form-control" min="1900" max="2024">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Copies</label>
                                <input type="number" name="copies" class="form-control" min="1" value="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g., A-101">
                    </div>
                    <button type="submit" name="add_book" class="btn btn-primary"><i class="fas fa-save"></i> Add Book</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            if (window.innerWidth <= 1024) sidebar.classList.toggle('mobile-open');
        }
        
        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => link.classList.remove('active'));
            
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
