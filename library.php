<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
$pageTitle = 'Library Management';

// Handle book operations
if ($_POST) {
    if (isset($_POST['add_book'])) {
        if ($_SESSION['user_role'] !== 'librarian') {
            echo "<script>alert('Access denied. Only librarians can add books.');</script>";
        } else {
            $stmt = $conn->prepare("INSERT INTO books (isbn, title, author, category, publisher, publication_year, total_copies, available_copies, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssiiss", $_POST['isbn'], $_POST['title'], $_POST['author'], $_POST['category'], $_POST['publisher'], $_POST['year'], $_POST['copies'], $_POST['copies'], $_POST['location']);
            if ($stmt->execute()) {
                echo "<script>alert('Book added successfully!');</script>";
            }
        }
    }
    
    if (isset($_POST['issue_book'])) {
        $book_id = $_POST['book_id'];
        $due_date = date('Y-m-d', strtotime('+14 days'));
        
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ? AND available_copies > 0");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $stmt = $conn->prepare("INSERT INTO book_transactions (book_id, student_name, student_id, transaction_type, issue_date, due_date) VALUES (?, ?, ?, 'issue', CURDATE(), ?)");
                $stmt->bind_param("isss", $book_id, $_POST['student_name'], $_POST['student_id'], $due_date);
                $stmt->execute();
                $conn->commit();
                echo "<script>alert('Book issued successfully!');</script>";
            } else {
                throw new Exception("Book not available");
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error: Book not available or already issued');</script>";
        }
    }
    
    if (isset($_POST['return_book'])) {
        $transaction_id = $_POST['transaction_id'];
        
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT book_id, due_date FROM book_transactions WHERE id = ? AND status = 'issued'");
            $stmt->bind_param("i", $transaction_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $fine = 0;
                if (date('Y-m-d') > $row['due_date']) {
                    $days_late = (strtotime(date('Y-m-d')) - strtotime($row['due_date'])) / (60 * 60 * 24);
                    $fine = $days_late * 2;
                }
                
                $stmt = $conn->prepare("UPDATE book_transactions SET status = 'returned', return_date = CURDATE(), fine_amount = ? WHERE id = ?");
                $stmt->bind_param("di", $fine, $transaction_id);
                $stmt->execute();
                
                $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
                $stmt->bind_param("i", $row['book_id']);
                $stmt->execute();
                
                $conn->commit();
                echo "<script>alert('Book returned successfully! Fine: $" . number_format($fine, 2) . "');</script>";
            }
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error returning book');</script>";
        }
    }
}

include 'header.php';
?>

<style>
.card {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid rgba(255,255,255,0.3);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.card-title {
    font-size: 1.5rem;
    color: #333;
    font-weight: 600;
}

.nav-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    border: none;
}

.nav-tabs .nav-link {
    background: rgba(255,255,255,0.7);
    border: 2px solid transparent;
    border-radius: 12px;
    padding: 12px 24px;
    color: #4a5568;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link.active,
.nav-tabs .nav-link:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateY(-2px);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102,126,234,0.3);
}

.btn-success {
    background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    color: white;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(72,187,120,0.3);
}

.table-responsive {
    overflow-x: auto;
    border-radius: 16px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

th {
    background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
    color: white;
    padding: 20px 16px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
}

td {
    padding: 16px;
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.2s ease;
}

tr:hover td {
    background-color: #f7fafc;
}

.badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-success {
    background: #c6f6d5;
    color: #22543d;
}

.badge-danger {
    background: #fed7d7;
    color: #742a2a;
}

.row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.col-md-6 {
    flex: 1;
}

.col-md-4 {
    flex: 1;
}
</style>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📚 Library Management System</h2>
        </div>
        
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <a href="#" class="nav-link active" onclick="showTab('books')">📖 Books</a>
            <a href="#" class="nav-link" onclick="showTab('issue')">📤 Issue Book</a>
            <a href="#" class="nav-link" onclick="showTab('return')">📥 Return Book</a>
            <?php if ($_SESSION['user_role'] === 'librarian'): ?>
            <a href="#" class="nav-link" onclick="showTab('add')">➕ Add Book</a>
            <?php endif; ?>
        </div>

        <!-- Books List -->
        <div id="books" class="tab-content active">
            <h5>📖 Available Books</h5>
            <div class="table-responsive">
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
            <h5>📤 Issue Book</h5>
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
                <button type="submit" name="issue_book" class="btn btn-primary">Issue Book</button>
            </form>
        </div>

        <!-- Return Book -->
        <div id="return" class="tab-content">
            <h5>📥 Return Book</h5>
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
                <button type="submit" name="return_book" class="btn btn-success">Return Book</button>
            </form>

            <h5 style="margin-top: 30px;">📋 Currently Issued Books</h5>
            <div class="table-responsive">
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
            <h5>➕ Add New Book</h5>
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
                <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.remove('active'));
    
    // Remove active class from all nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => link.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    
    // Add active class to clicked nav link
    event.target.classList.add('active');
}
</script>

</body>
</html>