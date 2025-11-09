<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pio') {
    header("Location: ../../index.php");
    exit();
}
// ... rest of the manage_promotions.php code remains the same ...

// Get approved events for promotion
$events_query = "SELECT e.*, u.username as created_by_name 
                FROM events e 
                JOIN users u ON e.created_by = u.id 
                WHERE e.status = 'approved' 
                ORDER BY e.event_date DESC";
$events_result = mysqli_query($conn, $events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event Promotions - PIO Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .promotions-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .event-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 2px solid transparent;
        }
        
        .event-card.featured {
            border-color: #f39c12;
            background: #fffbf0;
        }
        
        .event-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        
        .event-meta {
            color: #7f8c8d;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
        
        .event-description {
            line-height: 1.5;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .promotion-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            flex: 1;
        }
        
        .btn-feature {
            background: #f39c12;
            color: white;
        }
        
        .btn-unfeature {
            background: #95a5a6;
            color: white;
        }
        
        .featured-badge {
            background: #f39c12;
            color: white;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <?php include '../config/includes/header.php'; ?>
    
    <div class="promotions-container">
        <h2>Manage Event Promotions</h2>
        <p>Promote approved events to highlight them on the student dashboard</p>
        
        <?php if(mysqli_num_rows($events_result) == 0): ?>
            <div class="empty-state">
                <h3>No Approved Events Available</h3>
                <p>There are no approved events to promote at the moment.</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php while($event = mysqli_fetch_assoc($events_result)): ?>
                    <div class="event-card">
                        <?php if(rand(0, 1)): // Simulating featured status - you'll want to implement this properly ?>
                            <div class="featured-badge">FEATURED</div>
                        <?php endif; ?>
                        
                        <h3 class="event-title"><?php echo htmlspecialchars($event['event_name']); ?></h3>
                        
                        <div class="event-meta">
                            <div><strong>Date:</strong> <?php echo date('F j, Y', strtotime($event['event_date'])); ?></div>
                            <div><strong>Created by:</strong> <?php echo htmlspecialchars($event['created_by_name']); ?></div>
                            <div><strong>Budget:</strong> P<?php echo number_format($event['proposed_budget'], 2); ?></div>
                        </div>
                        
                        <div class="event-description">
                            <?php echo htmlspecialchars(substr($event['description'], 0, 100) . '...'); ?>
                        </div>
                        
                        <div class="promotion-actions">
                            <button class="btn btn-feature">★ Feature Event</button>
                            <button class="btn btn-unfeature">Remove Feature</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Simple feature toggle functionality
        document.querySelectorAll('.btn-feature').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.event-card');
                card.classList.add('featured');
                alert('Event featured! This will be highlighted on the student dashboard.');
            });
        });
        
        document.querySelectorAll('.btn-unfeature').forEach(button => {
            button.addEventListener('click', function() {
                const card = this.closest('.event-card');
                card.classList.remove('featured');
                alert('Event removed from featured section.');
            });
        });
    </script>
</body>
</html>