-- ActiveCore Gym Database Schema
-- Created for PHP MVC Gym Management System

-- Users table (base authentication table)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('member', 'coach', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Members table
CREATE TABLE members (
    member_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    membership_date DATE DEFAULT CURRENT_DATE,
    membership_status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Coaches table
CREATE TABLE coaches (
    coach_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    specialization VARCHAR(100),
    experience_years INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    phone VARCHAR(20),
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Equipment table
CREATE TABLE equipment (
    equipment_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('available', 'in_use', 'maintenance', 'out_of_order') DEFAULT 'available',
    purchase_date DATE,
    last_maintenance DATE,
    description TEXT
);

-- Exercises table
CREATE TABLE exercises (
    exercise_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    instructions TEXT NOT NULL,
    equipment_needed TEXT,
    muscle_group VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Exercise-Equipment relationship table
CREATE TABLE exercise_equipment (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exercise_id INT NOT NULL,
    equipment_id INT NOT NULL,
    FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(equipment_id) ON DELETE CASCADE,
    UNIQUE KEY unique_exercise_equipment (exercise_id, equipment_id)
);

-- Coach Sessions table
CREATE TABLE coach_sessions (
    session_id INT PRIMARY KEY AUTO_INCREMENT,
    coach_id INT NOT NULL,
    member_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE
);

-- Workout Tracking table
CREATE TABLE workout_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    exercise_id INT NOT NULL,
    workout_date DATE NOT NULL,
    sets INT NOT NULL,
    reps INT NOT NULL,
    weight DECIMAL(6,2),
    duration_minutes INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id) ON DELETE CASCADE
);

-- Announcements table
CREATE TABLE announcements (
    announcement_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    announcement_type ENUM('general', 'maintenance', 'promotion', 'event') DEFAULT 'general',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Coach Ratings table
CREATE TABLE coach_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    coach_id INT NOT NULL,
    member_id INT NOT NULL,
    session_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coach_id) REFERENCES coaches(coach_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (session_id) REFERENCES coach_sessions(session_id) ON DELETE CASCADE,
    UNIQUE KEY unique_session_rating (session_id)
);

-- Insert sample data for testing

-- Insert admin user
INSERT INTO users (username, email, password_hash, user_type) VALUES 
('admin', 'admin@activecore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample member
INSERT INTO users (username, email, password_hash, user_type) VALUES 
('johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member');

INSERT INTO members (user_id, first_name, last_name, phone) VALUES 
(2, 'John', 'Doe', '09123456789');

-- Insert sample coach
INSERT INTO users (username, email, password_hash, user_type) VALUES 
('sarahcoach', 'sarah@activecore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach');

INSERT INTO coaches (user_id, first_name, last_name, specialization, experience_years) VALUES 
(3, 'Sarah', 'Smith', 'Strength Training', 5);

-- Insert sample equipment
INSERT INTO equipment (name, category, status, description) VALUES 
('Treadmill', 'Cardio', 'available', 'Electric treadmill with speed and incline control'),
('Bench Press', 'Strength', 'available', 'Olympic bench press with barbell supports'),
('Dumbbells Set', 'Strength', 'available', 'Adjustable dumbbells from 5-50 lbs'),
('Pull-up Bar', 'Strength', 'available', 'Wall-mounted pull-up bar'),
('Yoga Mat', 'Flexibility', 'available', 'Non-slip exercise mat');

-- Insert sample exercises
INSERT INTO exercises (name, difficulty_level, instructions, equipment_needed, muscle_group) VALUES 
('Push-ups', 'beginner', 'Lie face down, place hands shoulder-width apart, push body up until arms are extended, then lower back down.', 'None', 'Chest'),
('Squats', 'beginner', 'Stand with feet shoulder-width apart, lower body by bending knees, keep back straight, return to standing position.', 'None', 'Legs'),
('Bench Press', 'intermediate', 'Lie on bench, grip barbell with hands slightly wider than shoulders, lower bar to chest, then press up.', 'Barbell, Bench', 'Chest'),
('Deadlifts', 'advanced', 'Stand with feet hip-width apart, bend at hips and knees to grab bar, stand up straight keeping back straight.', 'Barbell', 'Full Body'),
('Plank', 'beginner', 'Hold push-up position with forearms on ground, keep body straight, hold for specified time.', 'None', 'Core');

-- Insert sample announcements
INSERT INTO announcements (title, content, announcement_type, priority, created_by) VALUES 
('Welcome to ActiveCore Gym!', 'We are excited to have you join our fitness community. Check out our new equipment and training programs.', 'general', 'high', 1),
('Weekend Special Promotion', 'Get 20% off on personal training sessions this weekend only!', 'promotion', 'medium', 1);

-- Insert sample coach session
INSERT INTO coach_sessions (coach_id, member_id, session_date, session_time, status) VALUES 
(1, 1, '2026-03-20', '10:00:00', 'pending');

-- Workouts table (for workout plans created by admin)
CREATE TABLE workouts (
    workout_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    purpose ENUM('strength', 'cardio', 'flexibility', 'weight_loss', 'muscle_gain', 'endurance', 'recovery') NOT NULL,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    duration_minutes INT NOT NULL,
    equipment_required TEXT,
    instructions TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Workout-Exercise relationship table (to link exercises to workouts)
CREATE TABLE workout_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    workout_id INT NOT NULL,
    exercise_id INT NOT NULL,
    sets INT NOT NULL DEFAULT 1,
    reps INT,
    weight DECIMAL(6,2),
    duration_seconds INT,
    rest_seconds INT DEFAULT 60,
    order_index INT NOT NULL,
    notes TEXT,
    FOREIGN KEY (workout_id) REFERENCES workouts(workout_id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES exercises(exercise_id) ON DELETE CASCADE,
    UNIQUE KEY unique_workout_exercise_order (workout_id, exercise_id, order_index)
);

-- Member Workout Progress table
CREATE TABLE member_workout_progress (
    progress_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    workout_id INT NOT NULL,
    completion_date DATE NOT NULL,
    status ENUM('completed', 'partial', 'skipped') NOT NULL,
    notes TEXT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(member_id) ON DELETE CASCADE,
    FOREIGN KEY (workout_id) REFERENCES workouts(workout_id) ON DELETE CASCADE
);

-- Insert sample workouts
INSERT INTO workouts (title, description, purpose, difficulty_level, duration_minutes, equipment_required, instructions, created_by) VALUES 
('Beginner Full Body Strength', 'A comprehensive full-body workout perfect for beginners to build foundational strength', 'strength', 'beginner', 45, 'Dumbbells, Bench', 'Perform each exercise for the specified sets and reps. Focus on proper form and take adequate rest between sets.', 1),
('Advanced Cardio Blast', 'High-intensity cardio workout designed for experienced fitness enthusiasts', 'cardio', 'advanced', 30, 'Treadmill, Jump Rope', 'Alternate between high-intensity intervals and active recovery. Maintain proper breathing throughout.', 1),
('Flexibility & Recovery', 'Gentle stretching and mobility workout for recovery and injury prevention', 'recovery', 'beginner', 20, 'Yoga Mat, Foam Roller', 'Hold each stretch for 30-60 seconds. Breathe deeply and never push to the point of pain.', 1);

-- Insert sample workout-exercise relationships
INSERT INTO workout_exercises (workout_id, exercise_id, sets, reps, rest_seconds, order_index) VALUES 
(1, 1, 3, 12, 60, 1),  -- Beginner workout: Push-ups
(1, 2, 3, 15, 60, 2),  -- Beginner workout: Squats
(1, 5, 3, 30, 60, 3);  -- Beginner workout: Plank (30 seconds)

INSERT INTO workout_exercises (workout_id, exercise_id, sets, duration_seconds, rest_seconds, order_index) VALUES 
(2, 1, 5, 45, 30, 1),  -- Advanced cardio: Push-ups (45 seconds each)
(2, 2, 5, 60, 30, 2);  -- Advanced cardio: Squats (60 seconds each)
