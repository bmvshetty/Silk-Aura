<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Silk Aura - Seller Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { 
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
        }

        .top-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
        }

        .logo {
            font-size: 2.5em;
            color: #fff; 
            font-family: 'Playfair Display', serif; 
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3); 
        }

        nav {
            flex-grow: 1;
            display: flex;
            justify-content: flex-end;
        }

        nav ul { 
            list-style: none; 
            display: flex;
            gap: 20px;
            margin: 0;
            padding: 0;
        }  

        nav ul li a { 
            text-decoration: none;
            color: #fff;
            font-size: 1.2em;
            font-weight: bold;
            padding: 5px 10px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover { 
            color: rgba(2, 12, 15, 0.91); 
            transform: scale(1.1);
        }

        main {
            width: 100%; 
            height: calc(100vh - 60px);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            flex-direction: column; 
            text-align: center;
            background-image: url('images/background.jpg'); /* Use relative path */
            background-size: cover;
            background-position: center;
            padding: 20px;
            box-sizing: border-box;
        }

        .overlay {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 900px;
            box-sizing: border-box;
        }

        footer { 
            text-align: center; 
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            color: #777;
        }

        h1 {
            font-size: 4em;
            color: #333;
            margin-bottom: 20px;
        }

        .description {
            font-size: 1.2em;
            color: #333;
            margin-top: 20px;
            max-width: 700px;
            text-align: center;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .top-container {
                flex-direction: column;
                text-align: center;
            }

            nav ul {
                flex-direction: column;
                align-items: center;
            }

            nav ul li {
                margin: 10px 0;
            }

            h1 {
                font-size: 3em;
                color: rgb(255, 17, 0);
            }
        }
    </style>
</head>
<body>

<div class="top-container">
    <div class="logo">SILK <span>AURA</span></div>
    <nav>
        <ul>
            <li><a href="http://localhost/profile.html">Profile</a></li>
            <li><a href="http://localhost/products.html">Products</a></li>
            <li><a href="http://localhost/dashboard.html">Dashboard</a></li>
            <li><a href="http://localhost/shop_registration.html">Shop Registration</a></li>
            <li><a href="#" onclick="logout()">Logout</a></li> <!-- Fixed Logout -->
        </ul>
    </nav>
</div>

<main>
    <div class="overlay">
        <h1>Welcome to the Sellers Page</h1>
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
</script>

</body>
</html>
