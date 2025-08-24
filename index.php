<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? $_SESSION['username'] : '';
$email = $isLoggedIn ? $_SESSION['email'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EduSyncHub | AI-Powered Student Success Platform</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Montserrat:wght@800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <link rel="stylesheet" href="index.css" />
  <style>
    /* Account dropdown styles */
    .account-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
    }
    
    .account-btn {
      background: linear-gradient(135deg, var(--primary), var(--primary-light));
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .account-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .account-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      margin-top: 10px;
      min-width: 180px;
      overflow: hidden;
      display: none;
      z-index: 1001;
    }
    
    .dark-mode .account-dropdown {
      background: var(--dark-bg);
      border: 1px solid var(--dark-border);
    }
    
    .account-dropdown.show {
      display: block;
      animation: fadeIn 0.3s ease;
    }
    
    .account-info {
      padding: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .dark-mode .account-info {
      border-bottom: 1px solid var(--dark-border);
    }
    
    .account-username {
      font-weight: 600;
      margin-bottom: 5px;
    }
    
    .account-email {
      font-size: 0.85rem;
      color: #666;
    }
    
    .dark-mode .account-email {
      color: #aaa;
    }
    
    .dropdown-item {
      padding: 12px 15px;
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    
    .dropdown-item:hover {
      background: #f5f5f5;
    }
    
    .dark-mode .dropdown-item:hover {
      background: #2d3748;
    }
    
    .dropdown-item i {
      width: 16px;
    }
    
    .logout-item {
      color: #e53e3e;
    }
    
    /* Hide auth buttons when logged in */
    .logged-in .cta-buttons {
      display: none !important;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="<?php echo $isLoggedIn ? 'logged-in' : ''; ?>">
  <!-- Loading Screen -->
  <div id="loading-screen">
    <div class="loader-container">
      <div class="loader"></div>
      <div class="loader"></div>
      <div class="loader"></div>
    </div>
    <p class="loading-text">Initializing AI-Powered Learning Environment</p>
    <div class="loading-progress">
      <div class="progress-bar"></div>
    </div>
  </div>

  <!-- Theme Toggle -->
  <div class="theme-toggle" id="themeToggle">
    <i class="fas fa-moon"></i>
  </div>

  <!-- Account Dropdown (shown when logged in) -->
  <?php if ($isLoggedIn): ?>
    <div class="account-container">
      <button class="account-btn" id="accountBtn">
        <i class="fas fa-user-circle"></i>
        <span id="accountUsername"><?php echo htmlspecialchars($username); ?></span>
        <i class="fas fa-chevron-down"></i>
      </button>
      <div class="account-dropdown" id="accountDropdown">
        <div class="account-info">
          <div class="account-username"><?php echo htmlspecialchars($username); ?></div>
          <div class="account-email"><?php echo htmlspecialchars($email); ?></div>
        </div>
        <div class="dropdown-item logout-item" id="logoutBtn">
          <i class="fas fa-sign-out-alt"></i>
          <span>Logout</span>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <section class="hero1">
    <div class="hero1-content">
      <div class="mega-logo">
        <span class="logo-icon"><i class="fas fa-graduation-cap"></i></span>
        <h1 class="logo-text">EduSyncHub</h1>
        <p class="logo-tagline">AI-Powered Academic Success</p>
      </div>
    </div>
  </section>

  <!-- Welcome Message Container -->
  <?php if ($isLoggedIn && isset($_GET['welcome']) && $_GET['welcome'] === 'true'): ?>
    <div id="welcomeContainer" class="welcome-container">
      <div class="welcome-message">
        <div class="welcome-content">
          <i class="fas fa-user-graduate welcome-icon"></i>
          <div class="welcome-text">
            <h3>Welcome back, <span id="welcomeUsername"><?php echo htmlspecialchars($username); ?></span>!</h3>
            <p>Ready to continue your learning journey?</p>
          </div>
          <button class="welcome-close">&times;</button>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div id="welcomeContainer" class="welcome-container" style="display: none;"></div>
  <?php endif; ?>
  <div class="section-gap"></div>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content animate__animated animate__fadeInLeft">
      <h1>Your AI-Powered Academic Companion</h1>
      <p>EduSyncHub revolutionizes learning with smart scheduling, gamified challenges, and collaborative tools designed to maximize your success.</p>
      <div class="cta-buttons">
        <a href="login.php" class="btn btn-primary">
          <i class="fas fa-sign-in-alt"></i> Login
        </a>
        <a href="register.php" class="btn btn-secondary">
          <i class="fas fa-user-plus"></i> Register
        </a>
      </div>

      <div class="timetable-preview animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
        <div class="timetable-header">
          <h3><i class="fas fa-robot"></i> AI-Generated Smart Timetable</h3>
          <div class="timetable-options">
            <button class="option-btn active" id="focusWeak">Focus on Weak Subjects</button>
            <button class="option-btn" id="equalFocus">Balance All Subjects</button>
          </div>
        </div>
        
        <div class="timetable-grid" id="timetableGrid">
          <!-- Timetable will be generated here by JavaScript -->
        </div>
        
        <button class="generate-btn" id="generateTimetable">
          <i class="fas fa-sync-alt"></i> Generate New Timetable
        </button>
      </div>
      <a href="timetable.html" class="generate-btn">Go to page</a>
    </div>
    <img src="resources/f4cf44f8-8fee-4099-baa1-966a1dfb0aa7.svg" alt="Student Learning" class="hero-image animate__animated animate__fadeInRight">
  </section>

  <!-- Features Section -->
  <section class="features">
    <h2 class="section-title animate__animated animate__fadeIn">Why Students Love EduSyncHub</h2>
    <div class="features-grid">
      <div class="feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
        <div class="feature-icon">
          <i class="fas fa-robot"></i>
        </div>
        <h3>Smart AI Scheduling</h3>
        <p>Automatically generates optimized study plans based on your syllabus, deadlines, and performance.</p>
        <a href="timetable.html" style="color: var(--primary-light); font-weight: 600;">Try It Now →</a>
      </div>
      <div class="feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
        <div class="feature-icon">
          <i class="fas fa-gamepad"></i>
        </div>
        <h3>Interactive Quiz Arena</h3>
        <p>Earn Reward Points by competing in subject-based challenges with difficulty levels.</p>
        <a href="quiz.html" style="color: var(--primary-light); font-weight: 600;">Start Playing →</a>
      </div>
      <div class="feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
        <div class="feature-icon">
          <i class="fas fa-trophy"></i>
        </div>
        <h3>Competitive Leaderboards</h3>
        <p>Climb weekly rankings and unlock achievements based on your RP score.</p>
        <a href="leaderboard.html" style="color: var(--primary-light); font-weight: 600;">View Rankings →</a>
      </div>
      
      <div class="feature-card animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
        <div class="feature-icon">
          <i class="fas fa-book-open"></i>
        </div>
        <h3>Smart Resource Library</h3>
        <p>AI-curated textbooks, video lectures, and past papers tailored to your needs.</p>
        <a href="library.html" style="color: var(--primary-light); font-weight: 600;">Explore Resources →</a>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-links">
      <a href="about.html">About</a>
      <a href="contact.html">Contact</a>
      <a href="privacy.html">Privacy</a>
      <a href="terms.html">Terms</a>
    </div>
    <p>© 2025 EduSyncHub | Revolutionizing Education Through AI</p>
  </footer>

  <script>
    // Loading screen fade-out
    window.addEventListener('load', function() {
      setTimeout(function() {
        document.getElementById('loading-screen').classList.add('fade-out');
      }, 2500);
    });

    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const body = document.body;
    
    if (localStorage.getItem('theme') === 'dark') {
      body.classList.add('dark-mode');
      themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
    
    themeToggle.addEventListener('click', function() {
      body.classList.toggle('dark-mode');
      
      if (body.classList.contains('dark-mode')) {
        localStorage.setItem('theme', 'dark');
        themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
      } else {
        localStorage.setItem('theme', 'light');
        themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
      }
    });

    // Account dropdown functionality
    const accountBtn = document.getElementById('accountBtn');
    if (accountBtn) {
      accountBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('accountDropdown');
        dropdown.classList.toggle('show');
      });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      const dropdown = document.getElementById('accountDropdown');
      const accountBtn = document.getElementById('accountBtn');
      
      if (dropdown && accountBtn && !accountBtn.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
      }
    });

    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function() {
        fetch('logout.php', {
          method: 'POST',
          credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = 'index.php';
          }
        })
        .catch(error => console.error('Error:', error));
      });
    }

    // Close welcome message
    const welcomeClose = document.querySelector('.welcome-close');
    if (welcomeClose) {
      welcomeClose.addEventListener('click', function() {
        document.getElementById('welcomeContainer').style.display = 'none';
      });
    }

    // Auto-hide welcome message after 8 seconds
    const welcomeContainer = document.getElementById('welcomeContainer');
    if (welcomeContainer && welcomeContainer.style.display !== 'none') {
      setTimeout(() => {
        welcomeContainer.style.display = 'none';
      }, 8000);
    }

    // Animate elements when scrolling
    const animateOnScroll = function() {
      const elements = document.querySelectorAll('.animate__animated');
      elements.forEach(el => {
        const elementPosition = el.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        if (elementPosition < windowHeight - 100) {
          const animation = el.classList[1].split('animate__')[1];
          el.classList.add('animate__' + animation);
        }
      });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll();

    // Timetable data and generation
    const subjects = {
      weak: ["Math", "Science", "History"],
      average: ["Sinhala", "Literature", "Business Studies"],
      strong: ["English", "Religion", "ICT"]
    };
    
    const timeSlots = ["9:00-10:30", "11:00-12:30", "14:00-15:30", "16:00-17:30"];
    const days = ["Mon", "Tue", "Wed", "Thu", "Fri"];
    
    function generateTimetable(focusMode) {
      const timetableGrid = document.getElementById('timetableGrid');
      timetableGrid.innerHTML = '';
      
      days.forEach(day => {
        const dayElement = document.createElement('div');
        dayElement.className = 'timetable-day';
        dayElement.textContent = day;
        timetableGrid.appendChild(dayElement);
      });
      
      timeSlots.forEach(slot => {
        days.forEach(day => {
          const slotElement = document.createElement('div');
          slotElement.className = 'timetable-slot';
          
          let subject;
          if (focusMode === 'weak') {
            const rand = Math.random();
            subject = rand < 0.6 ? getRandomSubject('weak') :
                     rand < 0.9 ? getRandomSubject('average') :
                     getRandomSubject('strong');
          } else {
            const allSubjects = [...subjects.weak, ...subjects.average, ...subjects.strong];
            subject = allSubjects[Math.floor(Math.random() * allSubjects.length)];
          }
          
          slotElement.innerHTML = `
            <span class="subject">${subject}</span>
            <span class="time">${slot}</span>
            ${focusMode === 'weak' && subjects.weak.includes(subject) ? 
              '<span class="focus-badge"><i class="fas fa-bolt"></i> Focus</span>' : ''}
          `;
          
          timetableGrid.appendChild(slotElement);
        });
      });
    }
    
    function getRandomSubject(level) {
      const subjectList = subjects[level];
      return subjectList[Math.floor(Math.random() * subjectList.length)];
    }
    
    // Event listeners for timetable
    document.getElementById('focusWeak').addEventListener('click', function() {
      this.classList.add('active');
      document.getElementById('equalFocus').classList.remove('active');
      generateTimetable('weak');
    });
    
    document.getElementById('equalFocus').addEventListener('click', function() {
      this.classList.add('active');
      document.getElementById('focusWeak').classList.remove('active');
      generateTimetable('equal');
    });
    
    document.getElementById('generateTimetable').addEventListener('click', function() {
      const focusMode = document.querySelector('.option-btn.active').id === 'focusWeak' ? 'weak' : 'equal';
      this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
      
      setTimeout(() => {
        generateTimetable(focusMode);
        this.innerHTML = '<i class="fas fa-sync-alt"></i> Generate New Timetable';
      }, 800);
    });
    
    // Initial generation
    generateTimetable('weak');
  </script>
</body>
</html>