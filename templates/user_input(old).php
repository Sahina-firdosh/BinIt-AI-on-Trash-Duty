<?php 
    session_start(); 
    $php_err = "";
    $php_msg = " ";

    if (!isset($_SESSION['username'])) 
    {
        header('Location: /Major_Project/Login/login.php');
        exit();
    }
    else
    {
        $ses_username = $_SESSION['username'];       

        // Security headers to prevent attacks
        header("X-Frame-Options: DENY"); // Prevent clickjacking
        header("X-XSS-Protection: 1; mode=block"); // Prevent XSS
        header("X-Content-Type-Options: nosniff"); // Prevent MIME-type sniffing
    
        $servername = "localhost";
        $username = "root"; 
        $password = ""; 
        $dbname = "binit_db"; 
    
        $conn = new mysqli($servername, $username, $password, $dbname);
    
        if ($conn->connect_error) {
            $php_err = "Connection failed";
            exit();
        }
    
        $conn->set_charset("utf8mb4");
    
        $sql_create_table = "CREATE TABLE IF NOT EXISTS user_input_tb (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            latitude DECIMAL(10, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            area VARCHAR(255),
            city VARCHAR(255),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $conn->query($sql_create_table);
    
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"])) 
        {
            $target_dir = "garbage_img/";
    
            // Validate file type (only allow images)
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    
            if (!in_array($file_extension, $allowed_types)) {
                $php_err = "Invalid file type! Only JPG, PNG, and GIF allowed.";
                exit();
            }

            $latitude = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
            $longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;
            // $area = isset($_POST['area']) ? $_POST['area'] : 'Unknown';
            $area = 'Vasant Kunj';
            $city = isset($_POST['city']) ? $_POST['city'] : 'Unknown';

            $stmt = $conn->prepare("INSERT INTO user_input_tb (username, image_path, latitude, longitude, area, city) VALUES (?, '', ?, ?, ?, ?)");
            $stmt->bind_param("sddss", $ses_username, $latitude, $longitude, $area, $city);
    
            if ($stmt->execute()) 
            {
                $location_id = $stmt->insert_id;
                $stmt->close();

                    // Secure the filename
                    $target_file = $target_dir . $location_id . "." . $file_extension;
            
                    // Move the uploaded file securely
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $stmt = $conn->prepare("UPDATE user_input_tb SET image_path=? WHERE id=?");
                        $stmt->bind_param("si", $target_file, $location_id);
                        $stmt->execute();
                        $stmt->close();
            
                        $php_msg = "Image uploaded successfully with ID: $location_id";
                    } else {
                        $php_err =  "Error uploading image.";
                    }
            }
    
            $conn->close();
        }
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BinIt | User Input</title>
    <link rel="icon" href="../logo.png" type="image/x-icon">
    <link rel="stylesheet" href="/Major_Project/static/userinput_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <style>
        .profile-modal {
    display: none;
    position: fixed;
    top: 70px;
    right: 20px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    z-index: 1001;
    width: 220px;
}

.profile-modal.visible {
    display: block;
}

.profile-options {
    list-style: none;
    padding: 10px;
}

.profile-options li {
    padding: 10px;
    border-radius: 5px;
}

.profile-options li:hover {
    background-color: #f5f5f5;
}

.profile-options li a {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: #333;
}

.profile-options li img {
    height: 20px;
    width: 20px;
}
    </style>
</head>
<body>
    <nav>
        <div class="nav_left">
        <img src="../logo.png" alt="BinIt Logo" class="logo">
            <p>BinIt</p>
        </div>
        <div class="nav_right">
    <img src="../Main/user.png" alt="User-Profile" class="Profile_pic" id="profilePic">
    <div class="username" id="usernameDisplay"><?php echo $ses_username ?></div>
    <div id="profileModal" class="profile-modal">
        <div class="modal-content">
            <ul class="profile-options">
                <li>
                    <a href="#">
                        <img src="../Main/user_profile.png" alt="My Profile">
                        <span>My Profile</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <img src="../Main/change_pass.png" alt="Change Password">
                        <span>Change Password</span>
                    </a>
                </li>
                <li>
                    <a href="../Main/logout.php">
                        <img src="../Main/logout.png" alt="Log Out">
                        <span>Log Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
    </nav>

    <div class="sidebar_menu">
        <ul class="main_menu">
            <li>
                <a href="/Major_Project/Main/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> 
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="active">
                <a href="/Major_Project/templates/user_input.php">
                    <i class="fas fa-keyboard"></i> 
                    <span>User Input</span>
                </a>
            </li>
            <li>
                <a href="/Major_Project/templates/analysis_visual.php">
                    <i class="fas fa-chart-bar"></i> 
                    <span>Analysis & Visualization</span>
                </a>
            </li>
            <!-- <li>
                <a href="/Major_Project/Main/survey_feed.html">
                    <i class="fas fa-poll"></i> 
                    <span style="margin-left: 1vh;">Survey & Feedback</span>
                </a>
            </li> -->
            <li>
                <a href="/Major_Project/Main/help_support.php">
                    <i class="fas fa-question-circle"></i>
                    <span>Help & Support</span>
                </a>
            </li>
        </ul>
    </div>

<!-- ----------------------------------------------------------------------------------------------------------------------------- -->

    <div class="main-content">
        <div class="location-container">
            <h3>Current Location</h3>
            <div class="location-details" id="locationDetails">
                <div class="location-detail">Detecting your location...</div>
            </div>
            <!-- <form action="" method="post">
            <label for="manual_location">Enter Location (optional):</label>
            <input type="text" name="manual_location" id="manual_location" placeholder="City, Country">

            </form> -->
        </div>

        <div class="upload-preview-container"> 
            <div class="upload-container">
                <h2>Upload Waste Image</h2>
                <form action="#" method="post" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-btn-wrapper">
                        <button type="button" class="upload-btn" onclick="document.getElementById('imageUpload').click();">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="17 8 12 3 7 8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            Choose Image
                        </button>
                        <input type="file" name="image" id="imageUpload" accept="image/*" required style="display: none;"> 
                        <div class="php_msg"><?php echo isset($php_msg) ? $php_msg : ''; ?></div>
                        <div class="php_err"><?php echo isset($php_err) ? $php_err : ''; ?></div>
                        <div id="uploadStatus" style="margin-top: 10px;"></div>
                    </div>
                    
                    <!-- Hidden fields for location data -->
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="area" id="area">
                    <input type="hidden" name="city" id="city">
                    <input type="hidden" name="username" value="<?php echo $ses_username; ?>">

                    <div class="preview-container" id="previewContainer" style="display: none;">
                        <div class="image-preview" id="imagePreview">
                            <img id="uploadedImage" src="#" alt="Uploaded Image" style="display: none;">
                        </div>
                        <button id="reportButton" type="submit" class="upload-btn" style="display: none; margin: auto;">Report Image</button>
                    </div>
                </form>
            </div>
        </div>    
    </div>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const locationDetails = document.getElementById("locationDetails");
            const latitudeField = document.getElementById("latitude");
            const longitudeField = document.getElementById("longitude");
            const areaField = document.getElementById("area");
            const cityField = document.getElementById("city");
            const uploadInput = document.getElementById("imageUpload");
            const previewContainer = document.getElementById("previewContainer");
            const imagePreview = document.getElementById("uploadedImage");
            const reportButton = document.getElementById("reportButton");
            const uploadForm = document.getElementById("uploadForm");
            const uploadStatus = document.getElementById("uploadStatus");

            async function fetchLocation() {
                if ("geolocation" in navigator) {
                    navigator.geolocation.getCurrentPosition(async (position) => {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;

                        latitudeField.value = latitude;
                        longitudeField.value = longitude;

                        try {
                            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
                            const data = await response.json();

                            const area = data.address.suburb || data.address.neighbourhood || data.address.road || "N/A";
                            const city = data.address.city || data.address.town || data.address.state || "Unknown";

                            areaField.value = area;
                            cityField.value = city;

                            // locationDetails.innerHTML = `
                            //     <div class="location-detail">📍 ${area}, ${city}</div>
                            //     <div class="location-detail">📍 ${area}, ${city}</div>
                            //     <div class="location-detail">📌 Lat: ${latitude.toFixed(4)}</div>
                            //     <div class="location-detail">📌 Long: ${longitude.toFixed(4)}</div>
                            // `;
                            locationDetails.innerHTML = `
                                <div class="location-detail">📍 Vasant Kunj, ${city}</div>
                                <div class="location-detail">📌 Lat: ${latitude.toFixed(4)}</div>
                                <div class="location-detail">📌 Long: ${longitude.toFixed(4)}</div>
                            `;
                        } catch (error) {
                            console.error("Error fetching location details:", error);
                            locationDetails.innerHTML = `<div class="location-detail">⚠️ Location detection failed</div>`;
                        }
                    },
                    (error) => {
                        console.error("Geolocation error:", error);
                        locationDetails.innerHTML = `<div class="location-detail">⚠️ Location access denied</div>`;
                    });
                } else {
                    locationDetails.innerHTML = `<div class="location-detail">⚠️ Geolocation not supported</div>`;
                }
            }

            // Call location fetch function on page load
            fetchLocation();

            // Show image preview when file is selected
            uploadInput.addEventListener("change", function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = "block";
                        previewContainer.style.display = "block";
                        reportButton.style.display = "block";
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Handle form submission with AJAX
            // Handle form submission with AJAX
            // In your user_input.php JavaScript section
            reportButton.addEventListener("click", function(e) {
                e.preventDefault();

                const formData = new FormData(uploadForm);
                // uploadStatus.innerHTML = '<p style="color: blue;">Testing connection to Flask server...</p>';

                // First test if the server is reachable
                // console.log("Testing Flask server connection...");
                fetch("http://localhost:5000/test", {
                    mode: 'cors',
                    credentials: 'same-origin' 
                })
                .then(response => {
                    // console.log("Test endpoint response:", response);
                    // uploadStatus.innerHTML += '<p>Test connection successful!</p>';
                    
                    // Now try the actual image upload
                    // console.log("Uploading image to Flask server...");
                    // uploadStatus.innerHTML += '<p>Uploading image...</p>';
                    
                    return fetch("http://localhost:5000/process_image", {
                        method: "POST",
                        body: formData,
                        mode: 'cors',
                        credentials: 'same-origin'
                    });
                })
                .then(response => {
                    // console.log("Image upload response status:", response.status);
                    // console.log("Response headers:", [...response.headers.entries()]);
                    return response.json();
                })
                .then(data => {
                    // console.log("Success data:", data);
                    uploadStatus.innerHTML += '<p style="color: green;">Image upload successful!</p>';
                    
                    if (data.success) {
                        uploadStatus.innerHTML += '<p>Redirecting...</p>';
                        window.location.href = data.redirect;
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    uploadStatus.innerHTML += '<p style="color: red;">Error: ' + error.message + '</p>';
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
    const profileModal = document.getElementById('profileModal');
    const usernameDisplay = document.getElementById('usernameDisplay');
    const profilePic = document.getElementById('profilePic');
    
    // Show modal when clicking on username or profile pic
    function toggleModal(event) {
        event.stopPropagation();
        profileModal.classList.toggle('visible');
    }
    
    usernameDisplay.addEventListener('click', toggleModal);
    profilePic.addEventListener('click', toggleModal);
    
    // Close modal when clicking elsewhere
    document.addEventListener('click', function() {
        profileModal.classList.remove('visible');
    });
    
    // Prevent clicks inside modal from closing it
    profileModal.addEventListener('click', function(event) {
        event.stopPropagation();
    });
});
    </script>

</body>
</html>
