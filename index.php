<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I.R.I.S - Intelligent RFID Identification System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
        }
        
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .nav-links { display: flex; gap: 30px; }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        
        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        
        .hero {
            text-align: center;
            padding: 120px 0;
            position: relative;
        }
        
        .hero h1 {
            font-size: 5rem;
            margin-bottom: 20px;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            animation: glow 2s ease-in-out infinite alternate, slideInDown 1s ease;
        }
        
        .hero p {
            font-size: 1.5rem;
            margin-bottom: 50px;
            opacity: 0.9;
            animation: slideInUp 1s ease 0.3s both;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 18px 45px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            animation: slideInUp 1s ease 0.6s both;
        }
        
        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
        
        .features {
            padding: 100px 0;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(15px);
        }
        
        .section-title {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 60px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
        }
        
        .feature-card {
            background: rgba(255,255,255,0.15);
            padding: 50px 40px;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.4s ease;
            animation: slideInLeft 0.8s ease;
        }
        
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.4s; }
        
        .feature-card:hover {
            transform: translateY(-15px) scale(1.02);
            background: rgba(255,255,255,0.25);
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        
        .feature-icon {
            font-size: 4rem;
            margin-bottom: 25px;
            display: block;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
        }
        
        .feature-card h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .feature-card p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 8%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 65%;
            right: 8%;
            animation-delay: 3s;
        }
        
        .shape:nth-child(3) {
            width: 80px;
            height: 80px;
            top: 45%;
            left: 85%;
            animation-delay: 6s;
        }
        
        .shape:nth-child(4) {
            width: 120px;
            height: 120px;
            top: 85%;
            left: 15%;
            animation-delay: 2s;
        }
        
        @keyframes glow {
            from { text-shadow: 3px 3px 6px rgba(0,0,0,0.4), 0 0 30px rgba(255,255,255,0.3); }
            to { text-shadow: 3px 3px 6px rgba(0,0,0,0.4), 0 0 50px rgba(255,255,255,0.5); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(15px) rotate(240deg); }
        }
        
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-100px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(100px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 3rem; }
            .nav-links { display: none; }
            .features-grid { grid-template-columns: 1fr; }
            .feature-card { padding: 40px 30px; }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="container">
        <nav>
            <div class="logo">I.R.I.S</div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#about">About</a>
                <a href="login.php">Login</a>
            </div>
        </nav>
        
        <section class="hero">
            <h1>I.R.I.S</h1>
            <p>Intelligent RFID Identification System</p>
            <a href="login.php" class="cta-button">Get Started</a>
        </section>
        
        <section class="features" id="features">
            <h2 class="section-title">System Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-icon">📡</span>
                    <h3>RFID Tracking</h3>
                    <p>Real-time attendance monitoring using advanced RFID technology</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">📊</span>
                    <h3>Analytics Dashboard</h3>
                    <p>Comprehensive reporting and data visualization tools</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">📧</span>
                    <h3>Email Reports</h3>
                    <p>Automated daily attendance reports sent directly to administrators</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>