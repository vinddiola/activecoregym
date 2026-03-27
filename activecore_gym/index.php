<?php
/**
 * Main Entry Point - Landing Page
 * ActiveCore Gym Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Announcement.php';
require_once __DIR__ . '/models/Exercise.php';
require_once __DIR__ . '/models/Equipment.php';
require_once __DIR__ . '/models/Coach.php';
require_once __DIR__ . '/models/Member.php';

// Initialize models
$announcement = new Announcement();
$exercise = new Exercise();
$equipment = new Equipment();
$coach = new Coach();
$member = new Member();

// Get data for landing page
$latestAnnouncements = $announcement->getLatest(3);
$featuredExercises = $exercise->getFeaturedExercises(6);
$featuredEquipment = $equipment->getFeaturedEquipment(6);
$topCoaches = $coach->getTopRated(3);
$activeMembersCount = $member->getActiveCount();
$totalCoachesCount = count($coach->getAll());
$totalEquipmentCount = count($equipment->getAll());

// Redirect if already logged in
if (isLoggedIn()) {
    redirectBasedOnUserType();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ActiveCore Gym - Transform Your Body, Transform Your Life</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_URL; ?>css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="fas fa-dumbbell"></i> <strong>ActiveCore</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#exercises">Exercises</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#equipment">Equipment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#coaches">Coaches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#announcements">News</a>
                    </li>
                </ul>
                <div class="ms-3">
                    <a href="<?php echo BASE_URL; ?>views/auth/login.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>views/auth/register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="display-4 fw-bold text-gold mb-4">
                            Transform Your Body, <span class="text-light-gold">Transform Your Life</span>
                        </h1>
                        <p class="lead text-light-gold mb-4">
                            Join ActiveCore Gym and start your fitness journey today. 
                            State-of-the-art equipment, expert coaches, and a supportive community await you.
                        </p>
                        <div class="d-flex gap-3">
                            <a href="<?php echo BASE_URL; ?>views/auth/register.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-rocket me-2"></i>Start Your Journey
                            </a>
                            <a href="#about" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-info-circle me-2"></i>Learn More
                            </a>
                        </div>
                        <div class="stats mt-5">
                            <div class="row">
                                <div class="col-4">
                                    <h3 class="text-gold"><?php echo $activeMembersCount; ?>+</h3>
                                    <p class="text-light-gold">Active Members</p>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-gold"><?php echo $totalCoachesCount; ?>+</h3>
                                    <p class="text-light-gold">Expert Coaches</p>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-gold"><?php echo $totalEquipmentCount; ?>+</h3>
                                    <p class="text-light-gold">Equipment</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" 
                             alt="Gym Workout" class="img-fluid rounded-4 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-dark-gray">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h2 class="display-5 fw-bold mb-4">About <span class="text-gold">ActiveCore Gym</span></h2>
                    <p class="lead mb-4 text-light-gold">
                        Welcome to ActiveCore Gym, where fitness meets community. We're not just a gym - 
                        we're a family dedicated to helping you achieve your health and fitness goals.
                    </p>
                    <p class="mb-4 text-light-gold">
                        Founded with the vision of making fitness accessible to everyone, ActiveCore Gym provides 
                        a comprehensive fitness experience with state-of-the-art equipment, personalized training programs, 
                        and a supportive environment that motivates you to be your best self.
                    </p>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-gold me-3 mt-1"></i>
                                <div>
                                    <h6 class="text-light-gold">Expert Trainers</h6>
                                    <p class="text-dark-gold small mb-0">Certified professionals to guide you</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-gold me-3 mt-1"></i>
                                <div>
                                    <h6 class="text-light-gold">Modern Equipment</h6>
                                    <p class="text-dark-gold small mb-0">Latest fitness technology</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-gold me-3 mt-1"></i>
                                <div>
                                    <h6 class="text-light-gold">Flexible Hours</h6>
                                    <p class="text-dark-gold small mb-0">Open 24/7 for your convenience</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-gold me-3 mt-1"></i>
                                <div>
                                    <h6 class="text-light-gold">Community Support</h6>
                                    <p class="text-dark-gold small mb-0">Join our fitness family</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" 
                         alt="Gym Interior" class="img-fluid rounded-4 shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5 bg-gray">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our <span class="text-gold">Services</span></h2>
                <p class="lead text-light-gold">Comprehensive fitness solutions for all your needs</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-dumbbell fa-3x text-gold mb-3"></i>
                            <h5 class="card-title text-gold">Strength Training</h5>
                            <p class="card-text text-light-gold">Build muscle and increase strength with our comprehensive weight training programs and equipment.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-running fa-3x text-gold mb-3"></i>
                            <h5 class="card-title text-gold">Cardio Fitness</h5>
                            <p class="card-text text-light-gold">Improve your cardiovascular health with our state-of-the-art cardio machines and group classes.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-user-tie fa-3x text-gold mb-3"></i>
                            <h5 class="card-title text-gold">Personal Training</h5>
                            <p class="card-text text-light-gold">Get personalized workout plans and one-on-one guidance from our certified trainers.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-users fa-3x text-gold mb-3"></i>
                            <h5 class="card-title text-gold">Group Classes</h5>
                            <p class="card-text text-light-gold">Join energizing group fitness classes including yoga, Zumba, HIIT, and more.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-apple-alt fa-3x text-gold mb-3"></i>
                            <h5 class="card-title text-gold">Nutrition Counseling</h5>
                            <p class="card-text text-light-gold">Get expert advice on diet and nutrition to complement your fitness routine.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm service-card">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-chart-line fa-3x text-gold mb-3"></i>
                            <h5 class="card-title text-gold">Progress Tracking</h5>
                            <p class="card-text text-light-gold">Monitor your fitness journey with detailed workout logs and progress analytics.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Exercises Section -->
    <section id="exercises" class="py-5 bg-dark-gray">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Featured <span class="text-gold">Exercises</span></h2>
                <p class="lead text-light-gold">Discover exercises that match your fitness level</p>
            </div>
            <div class="row">
                <?php if (!empty($featuredExercises)): ?>
                    <?php foreach ($featuredExercises as $exercise): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-gold"><?php echo htmlspecialchars($exercise['name']); ?></h5>
                                    <span class="badge bg-<?php echo ($exercise['difficulty_level'] === 'beginner') ? 'success' : (($exercise['difficulty_level'] === 'intermediate') ? 'warning' : 'danger'); ?> mb-2">
                                        <?php echo ucfirst(htmlspecialchars($exercise['difficulty_level'])); ?>
                                    </span>
                                    <p class="card-text text-light-gold small">
                                        <?php echo htmlspecialchars(substr($exercise['instructions'], 0, 100)) . '...'; ?>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-dark-gold">
                                            <i class="fas fa-bullseye me-1"></i> <?php echo htmlspecialchars($exercise['muscle_group'] ?? 'General'); ?>
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-light-gold">No exercises available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo BASE_URL; ?>views/auth/register.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>View All Exercises
                </a>
            </div>
        </div>
    </section>

    <!-- Equipment Section -->
    <section id="equipment" class="py-5 bg-gray">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our <span class="text-gold">Equipment</span></h2>
                <p class="lead text-light-gold">State-of-the-art fitness equipment for your workouts</p>
            </div>
            <div class="row">
                <?php if (!empty($featuredEquipment)): ?>
                    <?php foreach ($featuredEquipment as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title text-gold"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <span class="badge bg-success mb-2">
                                        <i class="fas fa-check-circle me-1"></i> <?php echo ucfirst(htmlspecialchars($item['status'])); ?>
                                    </span>
                                    <p class="card-text text-light-gold small">
                                        <?php echo htmlspecialchars($item['category']); ?>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-dark-gold"><?php echo htmlspecialchars(substr($item['description'] ?? '', 0, 80)) . '...'; ?></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-light-gold">No equipment available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo BASE_URL; ?>views/auth/register.php" class="btn btn-primary">
                    <i class="fas fa-eye me-2"></i>View All Equipment
                </a>
            </div>
        </div>
    </section>

    <!-- Coaches Section -->
    <section id="coaches" class="py-5 bg-dark-gray">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Our Expert <span class="text-gold">Coaches</span></h2>
                <p class="lead text-light-gold">Meet our certified fitness professionals</p>
            </div>
            <div class="row">
                <?php if (!empty($topCoaches)): ?>
                    <?php foreach ($topCoaches as $coach): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-user-circle fa-4x text-gold"></i>
                                    </div>
                                    <h5 class="card-title text-gold"><?php echo htmlspecialchars($coach['first_name'] . ' ' . $coach['last_name']); ?></h5>
                                    <p class="text-light-gold small"><?php echo htmlspecialchars($coach['specialization'] ?? 'General Fitness'); ?></p>
                                    <div class="mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $coach['rating'] ? 'text-gold' : 'text-dark-gold'; ?>"></i>
                                        <?php endfor; ?>
                                        <small class="text-dark-gold">(<?php echo number_format($coach['rating'], 1); ?>)</small>
                                    </div>
                                    <p class="card-text text-light-gold small">
                                        <?php echo $coach['experience_years']; ?> years experience
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-light-gold">No coaches available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Announcements Section -->
    <section id="announcements" class="py-5 bg-gray">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Latest <span class="text-gold">Announcements</span></h2>
                <p class="lead text-light-gold">Stay updated with gym news and events</p>
            </div>
            <div class="row">
                <?php if (!empty($latestAnnouncements)): ?>
                    <?php foreach ($latestAnnouncements as $announcement): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <span class="badge bg-<?php echo ($announcement['priority'] === 'high') ? 'danger' : (($announcement['priority'] === 'medium') ? 'warning' : 'info'); ?> mb-2">
                                        <?php echo ucfirst(htmlspecialchars($announcement['priority'])); ?>
                                    </span>
                                    <h5 class="card-title text-gold"><?php echo htmlspecialchars($announcement['title']); ?></h5>
                                    <p class="card-text text-light-gold">
                                        <?php echo htmlspecialchars(substr($announcement['content'], 0, 120)) . '...'; ?>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-dark-gold">
                                            <i class="fas fa-calendar me-1"></i> <?php echo formatDate($announcement['created_at']); ?>
                                        </small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No announcements at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-gradient text-center">
        <div class="container">
            <h2 class="display-5 fw-bold text-white mb-4">Ready to Start Your Fitness Journey?</h2>
            <p class="lead text-light-gold mb-4">Join ActiveCore Gym today and transform your life</p>
            <div class="d-flex gap-3 justify-content-center">
                <a href="<?php echo BASE_URL; ?>views/auth/register.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-rocket me-2"></i>Join Now
                </a>
                <a href="<?php echo BASE_URL; ?>views/auth/login.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Member Login
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-gold">ActiveCore Gym</h5>
                    <p class="text-light-gold">Transform Your Body, Transform Your Life</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-light-gold mb-0">
                        &copy; <?php echo date('Y'); ?> ActiveCore Gym. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo ASSETS_URL; ?>js/script.js"></script>
</body>
</html>
