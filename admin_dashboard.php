<?php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Database functions
require_once 'admin_functions.php';

// Get dashboard statistics
$stats = getDashboardStatistics();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        // Handle system settings update
        $systemName = sanitize($_POST['system_name']);
        $supportEmail = sanitize($_POST['support_email']);
        
        // Update settings in database (example)
        updateSystemSettings($systemName, $supportEmail);
        $success = "System settings updated successfully!";
    }
}

// Get data for different sections
$customers = getAllCustomers();
$agents = getAllAgents();
$tickets = getAllTickets();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Support Ticket Management System</title>
    <link rel="stylesheet" href="admin_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <h1>Transport Support Management System</h1>
            <p>Comprehensive Administration & Monitoring</p>
            <div class="user-info">
                <span><i class="fas fa-user-shield"></i> Welcome, <?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></span>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="dashboard-nav">
        <div class="container">
            <ul class="nav-tabs">
                <li class="nav-item active" data-target="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
                <li class="nav-item" data-target="customer-management"><i class="fas fa-users"></i> Customer Management</li>
                <li class="nav-item" data-target="agent-management"><i class="fas fa-user-tie"></i> Agent Management</li>
                <li class="nav-item" data-target="ticket-overview"><i class="fas fa-ticket-alt"></i> Ticket Overview</li>
                <li class="nav-item" data-target="system-settings"><i class="fas fa-cogs"></i> System Settings</li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Section -->
    <section id="dashboard" class="dashboard-section active">
        <div class="container1">
            <h2>System Dashboard</h2>
            
            <?php if (isset($success)): ?>
                <div class="alert success"><?= $success ?></div>
            <?php endif; ?>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3>Total Customers</h3>
                        <p id="totalCustomers"><?= $stats['total_customers'] ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                    <div class="stat-info">
                        <h3>Total Agents</h3>
                        <p id="totalAgents"><?= $stats['total_agents'] ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-info">
                        <h3>Total Tickets</h3>
                        <p id="totalTickets"><?= $stats['total_tickets'] ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="stat-info">
                        <h3>Ticket Resolution Rate</h3>
                        <p id="resolutionRate"><?= $stats['resolution_rate'] ?>%</p>
                    </div>
                </div>
            </div>
            
            <div class="dashboard-charts">
                <div class="chart-container">
                    <h3>Monthly Ticket Volume</h3>
                    <div id="monthlyTicketChart" class="chart">
                        <!-- Chart will be rendered by JavaScript -->
                    </div>
                </div>
                <div class="chart-container">
                    <h3>Ticket Status Distribution</h3>
                    <div id="ticketStatusChart" class="chart">
                        <!-- Chart will be rendered by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Customer Management Section -->
    <section id="customer-management" class="dashboard-section">
        <div class="container1">
            <h2>Customer Management</h2>
            
            <div class="user-management-controls">
                <form method="GET" class="search-group">
                    <input type="text" name="search" id="customerSearch" placeholder="Search customers..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
                <button class="btn btn-add-user" id="addCustomerBtn">
                    <i class="fas fa-user-plus"></i> Add New Customer
                </button>
            </div>
            
            <div class="user-list" id="customerList">
                <?php foreach ($customers as $customer): ?>
                <div class="user-item">
                    <div class="user-info">
                        <h4><?= htmlspecialchars($customer['username']) ?></h4>
                        <p><?= htmlspecialchars($customer['email']) ?></p>
                        <span class="user-contact">Registered: <?= date('M d, Y', strtotime($customer['created_at'])) ?></span>
                    </div>
                    <div class="user-actions">
                        <a href="view_customer.php?id=<?= $customer['id'] ?>" class="btn btn-view">View Details</a>
                        <a href="delete_user.php?id=<?= $customer['id'] ?>&type=customer" class="btn btn-delete" onclick="return confirm('Are you sure?')">Remove</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Agent Management Section -->
    <section id="agent-management" class="dashboard-section">
        <div class="container1">
            <h2>Agent Management</h2>
            
            <div class="user-management-controls">
                <form method="GET" class="search-group">
                    <input type="text" name="search" id="agentSearch" placeholder="Search agents..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
                <button class="btn btn-add-user" id="addAgentBtn">
                    <i class="fas fa-user-plus"></i> Add New Agent
                </button>
            </div>
            
            <div class="user-list" id="agentList">
                <?php foreach ($agents as $agent): ?>
                <div class="user-item">
                    <div class="user-info">
                        <h4><?= htmlspecialchars($agent['username']) ?></h4>
                        <p><?= htmlspecialchars($agent['email']) ?></p>
                        <span class="user-department">Agent</span>
                    </div>
                    <div class="user-actions">
                        <a href="edit_agent.php?id=<?= $agent['id'] ?>" class="btn btn-edit">Edit Profile</a>
                        <a href="delete_user.php?id=<?= $agent['id'] ?>&type=agent" class="btn btn-delete" onclick="return confirm('Are you sure?')">Remove</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Ticket Overview Section -->
    <section id="ticket-overview" class="dashboard-section">
        <div class="container1">
            <h2>Ticket Management</h2>
            
            <div class="ticket-filters">
                <form method="GET" class="filter-group">
                    <label for="ticketStatusFilter">Status:</label>
                    <select id="ticketStatusFilter" name="status">
                        <option value="all" <?= ($_GET['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Tickets</option>
                        <option value="open" <?= ($_GET['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="in-progress" <?= ($_GET['status'] ?? '') === 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="resolved" <?= ($_GET['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                        <option value="escalated" <?= ($_GET['status'] ?? '') === 'escalated' ? 'selected' : '' ?>>Escalated</option>
                    </select>
                    <button type="submit" class="btn">Filter</button>
                </form>
                <form method="GET" class="search-group">
                    <input type="text" name="search" id="ticketSearch" placeholder="Search tickets..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn">Search</button>
                </form>
            </div>
            
            <div class="ticket-list" id="ticketList">
                <?php foreach ($tickets as $ticket): ?>
                <div class="ticket-item">
                    <div class="ticket-header">
                        <h3>#<?= $ticket['id'] ?> - <?= htmlspecialchars($ticket['title']) ?></h3>
                        <span class="ticket-status <?= $ticket['status'] ?>"><?= ucfirst($ticket['status']) ?></span>
                    </div>
                    <div class="ticket-details">
                        <p><strong>Customer:</strong> <?= htmlspecialchars($ticket['customer_name']) ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars(substr($ticket['description'], 0, 100)) ?><?= strlen($ticket['description']) > 100 ? '...' : '' ?></p>
                        <p><strong>Assigned To:</strong> <?= $ticket['agent_name'] ?? 'Unassigned' ?></p>
                    </div>
                    <div class="ticket-actions">
                        <a href="view_ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-view">View Details</a>
                        <a href="assign_ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-assign">Reassign</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- System Settings Section -->
    <section id="system-settings" class="dashboard-section">
        <div class="container">
            <h2>System Configuration</h2>
            
            <form method="POST" class="settings-container">
                <div class="settings-group">
                    <h3>General Settings</h3>
                    <div class="form-group">
                        <label for="systemName">System Name</label>
                        <input type="text" id="systemName" name="system_name" value="Transport Support System">
                    </div>
                    <div class="form-group">
                        <label for="supportEmail">Support Email</label>
                        <input type="email" id="supportEmail" name="support_email" value="support@transport.com">
                    </div>
                </div>
                
                <div class="settings-group">
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" id="currentPassword" name="current_password" placeholder="Enter current password">
                    </div>
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" id="newPassword" name="new_password" placeholder="Enter new password">
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" id="confirmPassword" name="confirm_password" placeholder="Confirm new password">
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" name="save_settings" class="btn btn-save-settings">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?= date('Y') ?> Transport Support System. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
        // Tab Navigation
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            const dashboardSections = document.querySelectorAll('.dashboard-section');
            
            navItems.forEach(item => {
                item.addEventListener('click', function() {
                    navItems.forEach(tab => tab.classList.remove('active'));
                    dashboardSections.forEach(section => section.classList.remove('active'));
                    
                    this.classList.add('active');
                    
                    const targetId = this.getAttribute('data-target');
                    const targetSection = document.getElementById(targetId);
                    if (targetSection) {
                        targetSection.classList.add('active');
                    }
                });
            });
            
            // Add button functionality
            document.getElementById('addCustomerBtn')?.addEventListener('click', function() {
                // Show add customer form (implementation needed)
                alert('Add customer form would appear here');
            });
            
            document.getElementById('addAgentBtn')?.addEventListener('click', function() {
                // Show add agent form (implementation needed)
                alert('Add agent form would appear here');
            });
        });
    </script>
</body>
</html>