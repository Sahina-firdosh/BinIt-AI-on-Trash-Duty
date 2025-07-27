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
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BinIt | User Input</title>
    <link rel="icon" href="/Major_Project/static/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="/Major_Project/static/userinput_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
</head>
<body>
    <nav>
        <div class="nav_left">
            <img src="/Major_Project/static/logo.png" alt="BinIt Logo" class="logo">
            <p>BinIt</p>
        </div>
        <div class="nav_right">
            <img src="/Major_Project/static/user.png" alt="User-Profile" class="Profile_pic" id="profilePic">
            <div class="username" id="username"><?php echo $ses_username ?>
                <ul class="profile_card" id="profileMenu">
                    <li>
                        <a href="#">
                            <img src="/Major_Project/static/user_profile.png" alt="My Profile">
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <img src="/Major_Project/static/change_pass.png" alt="Change Password">
                            <span>Change Password</span>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <img src="/Major_Project/static/logout.png" alt="Log Out">
                            <span>Log Out</span>
                        </a>
                    </li>
                </ul>
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
            <li>
                <a href="/Major_Project/Main/survey_feed.html">
                    <i class="fas fa-poll"></i> 
                    <span style="margin-left: 1vh;">Survey & Feedback</span>
                </a>
            </li>
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
        </div>

        <div class="upload-preview-container"> 
            <div class="upload-container">
                <h2>Upload Waste Image</h2>
                <form action="http://localhost:5000/process_image" method="post" enctype="multipart/form-data" id="uploadForm">
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
                        <button id="reportButton" type="button" class="upload-btn" style="display: none; margin: auto;">Report Image</button>
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

                            locationDetails.innerHTML = `
                                <div class="location-detail">📍 ${area}, ${city}</div>
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
            reportButton.addEventListener("click", function(e) {
                e.preventDefault();

                // Ensure all fields are filled before submission
                if (!latitudeField.value || !longitudeField.value || !areaField.value || !cityField.value) {
                    uploadStatus.innerHTML = '<p style="color: red;">Please wait while location is being detected...</p>';
                    return;
                }

                const formData = new FormData(uploadForm);
                uploadStatus.innerHTML = '<p style="color: blue;">Uploading and processing image...</p>';

                fetch("http://localhost:5000/process_image", {
                    method: "POST",
                    body: formData,
                    mode: 'cors'
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || `HTTP error! Status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        uploadStatus.innerHTML = '<p style="color: green;">Success! Redirecting...</p>';
                        window.location.href = data.redirect;
                    } else {
                        uploadStatus.innerHTML = '<p style="color: red;">Error: ' + (data.error || 'Unknown error') + '</p>';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    uploadStatus.innerHTML = '<p style="color: red;">Error: ' + error.message + '</p>';
                });
            });
        });
    </script>

</body>
</html>
