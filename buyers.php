<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Silk Aura - Buyers Page</title>

    <!-- Font Awesome for Cart Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; }

        .top-container { 
            display: flex; justify-content: space-between; align-items: center; 
            padding: 20px 40px; background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b); 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .logo { font-size: 2.5em; color: #fff; font-family: 'Playfair Display', serif; text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3); }

        nav ul { list-style: none; display: flex; gap: 20px; margin: 0; padding: 0; }

        nav ul li a { text-decoration: none; color: #fff; font-size: 1.2em; font-weight: bold; padding: 5px 10px; transition: all 0.3s ease; }

        nav ul li a:hover { color: rgba(2, 12, 15, 0.91); transform: scale(1.1); }

        main { 
            width: 100%; height: calc(100vh - 60px); 
            display: flex; justify-content: center; align-items: center; flex-direction: column; 
            text-align: center; background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.9));
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); padding: 50px;
        }

        .main-content {
            background-color: #fff; padding: 30px; border-radius: 8px; 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); max-width: 700px; width: 100%;
            text-align: center;
        }

        .main-content h1 {
            font-family: 'Playfair Display', serif; font-size: 3em; color: #ff7e5f; 
            letter-spacing: 2px; margin-bottom: 20px;
        }

        .main-content p {
            font-size: 1.2em; color: #333; line-height: 1.6; margin-bottom: 20px;
        }

        .button {
            padding: 12px 25px; background-color: #4CAF50; color: white; text-decoration: none; 
            border-radius: 5px; font-weight: bold; transition: 0.3s; margin-top: 20px;
        }

        .button:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }

        footer { text-align: center; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; color: #777; }

        .background-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.2); z-index: -1;
        }
    </style>
</head>
<body>

    <div class="top-container">
        <div class="logo">SILK <span>AURA</span></div>
        <nav>
            <ul>
                <!-- Cart Icon -->
                <li><a href="#"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <!-- Logout Button -->
                <li><a href="#" onclick="logout()">Logout</a></li>
            </ul>
        </nav>
    </div>

    <main>
        <div class="main-content">
            <h1>Welcome to the Buyers Page</h1>
            <p>Explore a variety of silk products tailored to your needs. Our collection features the finest silk fabrics sourced from South India.</p>
            <p><a href="#" class="button">Browse Silk Products</a></p>
        </div>
    </main>

    <footer>
        <p>&copy; Silk Aura</p>
    </footer>

    <script>
        function logout() {
            if (confirm("Are you sure you want to logout?")) {
                // Clear session storage
                sessionStorage.clear();

                // Redirect to home page
                window.location.replace("/home.html");

                // Add new history state to prevent back navigation
                setTimeout(function() {
                    window.history.pushState(null, null, window.location.href);  // New history state to prevent going back
                    window.history.forward(); // Ensure back navigation is blocked
                }, 0);
            }
        }

        // Prevent back navigation after logout
        window.onload = function () {
            if (sessionStorage.getItem("loggedOut") === "true") {
                sessionStorage.removeItem("loggedOut");
                window.location.replace("/home.html");
            }
        };

        // Prevent back button from working on "1st page" after logout
        window.history.pushState(null, null, window.location.href); // Add a history entry
        window.onpopstate = function () {
            sessionStorage.setItem("loggedOut", "true");
            window.location.replace("/home.html"); // Force redirect if the back button is pressed
        };
    </script>

    <!-- Background overlay to darken the page behind the main content -->
    <div class="background-overlay"></div>
</body>
</html>