<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.html");
    exit();
}

require_once 'config.php'; // Database configuration

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get ticket statistics
$ticket_stats = [
    'total' => 0,
    'open' => 0,
    'in_progress' => 0,
    'resolved' => 0
];

try {
    // Total tickets
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $ticket_stats['total'] = $stmt->fetchColumn();

    // Open tickets
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status = 'Open'");
    $stmt->execute([$user_id]);
    $ticket_stats['open'] = $stmt->fetchColumn();

    // In Progress tickets
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status = 'In Progress'");
    $stmt->execute([$user_id]);
    $ticket_stats['in_progress'] = $stmt->fetchColumn();

    // Resolved tickets
    $stmt = $conn->prepare("SELECT COUNT(*) FROM tickets WHERE user_id = ? AND status = 'Resolved'");
    $stmt->execute([$user_id]);
    $ticket_stats['resolved'] = $stmt->fetchColumn();

    // Get recent tickets
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get user profile
    $stmt = $conn->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_profile = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    // Handle error appropriately
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Customer Support Ticket System</title>
    <!-- External Stylesheet -->
    <link rel="stylesheet" href="user_dash.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="container">
            <h1>Customer Support Ticket System</h1>
            <p>Report and manage your transport-related issues with ease.</p>
            <div class="user-info">
                <span><i class="fas fa-user"></i> Welcome, <span id="username"><?php echo htmlspecialchars($username); ?></span></span>
                <button id="logoutBtn" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="dashboard-nav">
        <div class="container">
            <ul class="nav-tabs">
                <li class="nav-item active" data-target="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
                <li class="nav-item" data-target="new-ticket"><i class="fas fa-plus-circle"></i> New Ticket</li>
                <li class="nav-item" data-target="my-tickets"><i class="fas fa-ticket-alt"></i> My Tickets</li>
                <li class="nav-item" data-target="profile"><i class="fas fa-user-cog"></i> Profile</li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Overview Section -->
    <section id="dashboard" class="dashboard-section active">
        <div class="container">
            <h2>Dashboard Overview</h2>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-info">
                        <h3>Total Tickets</h3>
                        <p id="totalTickets"><?php echo $ticket_stats['total']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-spinner"></i></div>
                    <div class="stat-info">
                        <h3>Open Tickets</h3>
                        <p id="openTickets"><?php echo $ticket_stats['open']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3>In Progress</h3>
                        <p id="inProgressTickets"><?php echo $ticket_stats['in_progress']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3>Resolved</h3>
                        <p id="resolvedTickets"><?php echo $ticket_stats['resolved']; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="recent-activity">
                <h3>Recent Activity</h3>
                <div class="activity-list" id="recentActivity">
                    <?php if (empty($recent_tickets)): ?>
                        <p>No recent activity</p>
                    <?php else: ?>
                        <?php foreach ($recent_tickets as $ticket): ?>
                            <div class="activity-item">
                                Ticket #<?php echo $ticket['id']; ?> - <?php echo $ticket['subject']; ?> (<?php echo $ticket['status']; ?>)
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- New Ticket Section -->
    <section id="new-ticket" class="dashboard-section">
        <div class="container">
            <h2>Create a New Ticket</h2>
            <p>Please select your transport type and provide details about the issue.</p>

            <form id="ticketForm" action="submit_ticket.php" method="POST" enctype="multipart/form-data">
                <!-- Transport Selection -->
                <div class="form-group">
                    <label for="transportType">Transport Type</label>
                    <select id="transportType" name="transportType" required>
                        <option value="" disabled selected>Select Transport Type</option>
                        <option value="bus">Bus</option>
                        <option value="train">Train</option>
                    </select>
                </div>

                <!-- Common Ticket Fields -->
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="Brief description of your issue" required>
                </div>

                <div class="form-group">
                    <label for="issueType">Issue Type</label>
                    <select id="issueType" name="issueType" required>
                        <option value="" disabled selected>Select Issue Type</option>
                        <option value="delay">Delay</option>
                        <option value="cancellation">Cancellation</option>
                        <option value="lostItem">Lost Item</option>
                        <option value="overcrowding">Overcrowding</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ticketNumber">Ticket Number (if applicable)</label>
                    <input type="text" id="ticketNumber" name="ticketNumber" placeholder="Enter your ticket number">
                </div>

                <div class="form-group">
                    <label for="incidentDate">Date of Incident</label>
                    <input type="date" id="incidentDate" name="incidentDate" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe the issue in detail" required></textarea>
                </div>

                <div class="form-group">
                    <label for="evidence">Upload Evidence</label>
                    <input type="file" id="evidence" name="evidence" accept="image/*, .pdf">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn submit-ticket">Submit Ticket</button>
                </div>
            </form>
        </div>
    </section>

    <!-- My Tickets Section -->
    <section id="my-tickets" class="dashboard-section">
        <div class="container">
            <h2>My Tickets</h2>
            
            <div class="ticket-filters">
                <div class="filter-group">
                    <label for="statusFilter">Filter by Status:</label>
                    <select id="statusFilter">
                        <option value="all">All Tickets</option>
                        <option value="open">Open</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="searchTicket">Search:</label>
                    <input type="text" id="searchTicket" placeholder="Search by ticket number or keywords">
                </div>
            </div>
            
            <div class="ticket-list" id="ticketList">
                <?php
                try {
                    $stmt = $conn->prepare("SELECT * FROM tickets WHERE user_id = ? ORDER BY created_at DESC");
                    $stmt->execute([$user_id]);
                    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($tickets)): ?>
                        <div class="no-tickets-message">You haven't submitted any tickets yet.</div>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <div class="ticket-item" data-ticket-id="<?php echo $ticket['id']; ?>" data-status="<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                <div class="ticket-header">
                                    <span class="ticket-number">Ticket #<?php echo $ticket['id']; ?></span>
                                    <span class="ticket-status status-<?php echo strtolower(str_replace(' ', '-', $ticket['status'])); ?>">
                                        <?php echo $ticket['status']; ?>
                                    </span>
                                </div>
                                <div class="ticket-content">
                                    <h4 class="ticket-title"><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                    <p class="ticket-description"><?php echo htmlspecialchars(substr($ticket['description'], 0, 100) . '...'); ?></p>
                                    <div class="ticket-meta">
                                        <span class="ticket-date"><i class="far fa-calendar-alt"></i> <?php echo date('m/d/Y', strtotime($ticket['created_at'])); ?></span>
                                        <span class="ticket-transport"><i class="fas fa-<?php echo $ticket['transport_type'] === 'bus' ? 'bus' : 'train'; ?>"></i> <?php echo ucfirst($ticket['transport_type']); ?></span>
                                    </div>
                                </div>
                                <div class="ticket-actions">
                                    <button class="btn-view" data-ticket-id="<?php echo $ticket['id']; ?>"><i class="fas fa-eye"></i> View</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php } catch (PDOException $e) {
                    error_log("Database error: " . $e->getMessage());
                    echo '<div class="error-message">Error loading tickets. Please try again later.</div>';
                } ?>
            </div>
        </div>
    </section>

    <!-- Ticket Detail Modal -->
    <div id="ticketDetailModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div id="ticketDetailContent">
                <!-- Ticket details will be populated here via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Profile Section -->
    <section id="profile" class="dashboard-section">
        <div class="container">
            <h2>My Profile</h2>
            
            <div class="profile-container">
                <div class="profile-info">
                    <form id="profileForm" action="update_profile.php" method="POST">
                        <div class="form-group">
                            <label for="fullName">Full Name</label>
                            <input type="text" id="fullName" name="fullName" placeholder="Your full name" value="<?php echo htmlspecialchars($user_profile['full_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Your email address" value="<?php echo htmlspecialchars($user_profile['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="Your phone number" value="<?php echo htmlspecialchars($user_profile['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Update Profile</button>
                        </div>
                    </form>
                </div>
                
                <div class="change-password">
                    <h3>Change Password</h3>
                    <form id="passwordForm" action="change_password.php" method="POST">
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="currentPassword" placeholder="Enter current password" required>
                        </div>
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Customer Support Ticket System. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- JavaScript for Dynamic Dashboard -->
    <script>
        // Tab Navigation
        const navItems = document.querySelectorAll('.nav-item');
        const dashboardSections = document.querySelectorAll('.dashboard-section');

        navItems.forEach(item => {
            item.addEventListener('click', function() {
                // Remove active class from all tabs and sections
                navItems.forEach(tab => tab.classList.remove('active'));
                dashboardSections.forEach(section => section.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Show corresponding section
                const targetSection = document.getElementById(this.getAttribute('data-target'));
                if (targetSection) {
                    targetSection.classList.add('active');
                }
            });
        });

        // Filter tickets by status
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const tickets = document.querySelectorAll('.ticket-item');
            
            tickets.forEach(ticket => {
                if (status === 'all' || ticket.getAttribute('data-status') === status) {
                    ticket.style.display = 'block';
                } else {
                    ticket.style.display = 'none';
                }
            });
        });

        // Search tickets
        document.getElementById('searchTicket').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tickets = document.querySelectorAll('.ticket-item');
            
            tickets.forEach(ticket => {
                const ticketText = ticket.textContent.toLowerCase();
                if (ticketText.includes(searchTerm)) {
                    ticket.style.display = 'block';
                } else {
                    ticket.style.display = 'none';
                }
            });
        });

        // Logout functionality
        document.getElementById('logoutBtn').addEventListener('click', function() {
            window.location.href = 'logout.php';
        });

        // View ticket details
        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-ticket-id');
                fetchTicketDetails(ticketId);
            });
        });

        // Fetch ticket details via AJAX
        function fetchTicketDetails(ticketId) {
            fetch('get_ticket_details.php?id=' + ticketId)
                .then(response => response.json())
                .then(data => {
                    displayTicketDetails(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Display ticket details in modal
        function displayTicketDetails(ticket) {
            const modal = document.getElementById('ticketDetailModal');
            const content = document.getElementById('ticketDetailContent');
            
            const detailHTML = `
                <h3>Ticket #${ticket.id}</h3>
                <div class="ticket-detail-header">
                    <span class="ticket-detail-status status-${ticket.status.toLowerCase().replace(' ', '-')}">Status: ${ticket.status}</span>
                    <span class="ticket-detail-date">Submitted: ${new Date(ticket.created_at).toLocaleDateString()}</span>
                </div>
                <div class="ticket-detail-body">
                    <div class="detail-section">
                        <h4>Issue Details</h4>
                        <p><strong>Subject:</strong> ${ticket.subject}</p>
                        <p><strong>Transport Type:</strong> ${ticket.transport_type}</p>
                        <p><strong>Issue Type:</strong> ${ticket.issue_type}</p>
                        <p><strong>Date of Incident:</strong> ${ticket.incident_date}</p>
                        <p><strong>Description:</strong> ${ticket.description}</p>
                    </div>
                    <div class="detail-section">
                        <h4>Conversation</h4>
                        <div class="conversation-thread" id="conversationThread">
                            <!-- Replies will be loaded here -->
                        </div>
                        <div class="reply-section">
                            <h5>Add Reply</h5>
                            <textarea class="reply-textarea" placeholder="Type your message here..."></textarea>
                            <button class="btn reply-btn" data-ticket-id="${ticket.id}">Send Reply</button>
                        </div>
                    </div>
                </div>
            `;
            
            content.innerHTML = detailHTML;
            modal.style.display = 'block';
            
            // Load replies
            fetchTicketReplies(ticket.id);
            
            // Add event listener to close modal
            document.querySelector('.close-modal').addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Fetch ticket replies
        function fetchTicketReplies(ticketId) {
            fetch('get_ticket_replies.php?id=' + ticketId)
                .then(response => response.json())
                .then(replies => {
                    const thread = document.getElementById('conversationThread');
                    if (replies.length > 0) {
                        let repliesHTML = '';
                        replies.forEach(reply => {
                            repliesHTML += `
                                <div class="message ${reply.user_role === 'agent' ? 'agent-message' : 'customer-message'}">
                                    <div class="message-header">
                                        <span class="message-sender">${reply.username} (${reply.user_role})</span>
                                        <span class="message-time">${new Date(reply.created_at).toLocaleString()}</span>
                                    </div>
                                    <div class="message-body">
                                        ${reply.message}
                                    </div>
                                </div>
                            `;
                        });
                        thread.innerHTML = repliesHTML;
                    } else {
                        thread.innerHTML = '<p>No replies yet.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Initialize the dashboard
        window.addEventListener('load', function() {
            // Any initialization code can go here
        });
    </script>
</body>
</html>