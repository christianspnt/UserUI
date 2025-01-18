<?php
// Include the database connection file
session_start();
include_once('connect.php');

$account_ID = $_SESSION['user_id']; // The logged-in user's account ID

// If the form is submitted, update the user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the POST data from the form
    $fName = $_POST['fName'];
    $lName = $_POST['lName'];
    $email = $_POST['email'];
    $phone_num = $_POST['phone_num'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];

    // Prepare the SQL query to update the user details
    $query = "UPDATE passenger_accounts SET fName = :fName, lName = :lName, email = :email, phone_num = :phone_num, 
                address = :address, DOB = :dob, age = :age, sex = :sex WHERE account_ID = :account_ID";
    $stmt = $conn->prepare($query);

    // Bind parameters
    $stmt->bindParam(':fName', $fName);
    $stmt->bindParam(':lName', $lName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone_num', $phone_num);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':dob', $dob);
    $stmt->bindParam(':age', $age);
    $stmt->bindParam(':sex', $sex);
    $stmt->bindParam(':account_ID', $account_ID);

    // Execute the update query
    if ($stmt->execute()) {
        echo "<script>alert('Details updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating details.');</script>";
    }

}

// Fetch the user details from the passenger_accounts table based on account_ID
$query = "SELECT * FROM passenger_accounts WHERE account_ID = :account_ID AND status = 'Active'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
$stmt->execute();
$userDetails = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$userDetails) {
    echo "Error: User details not found.";
    exit;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: radial-gradient(circle at top left, #4f7198, #f9ce80, #4f7198);
            background-position: top left;
            background-size: cover;
            height: 100vh;
            overflow: hidden;
        }

        .header-sttng {
            display: flex;
            align-items: center;
            padding: 10px;
            width: 100%;
        }

        .header-sttng button {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            margin-right: 20px;
        }

        .header-sttng h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .container-sttng {
            display: flex;
            width: 98%;
            margin-top: 30px;
        }

        .left-side-sttng {
            width: 30%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .left-side-sttng button {
            width: 90%;
            padding: 10px;
            font-size: 16px;
            margin-left: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .left-side-sttng button:hover {
            background-color: #f0f0f0;
        }

        .vertical-hr-sttng {
            width: 2px;
            background-color: #ccc;
            margin-right: 30px;
        }

        .right-side-sttng {
            width: 80%;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .right-side-sttng h2 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .input-group-sttng {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .input-group-sttng input {
            flex: 1;
            padding: 10px;
            border: none;
            border-bottom: 2px solid #ccc;
            font-size: 16px;
            transition: border-bottom-color 0.3s;
        }

        .input-group-sttng input:focus {
            outline: none;
            border-bottom-color: #4f7198;
        }

        .input-group-sttng input[readonly] {
            background-color: #f9f9f9;
            color: #666;
        }

        .label-group-sttng {
            display: flex;
            gap: 20px;
            justify-content: space-between;
        }

        .label-group-sttng label {
            flex: 1;
            text-align: left;
            font-size: 12px;
            color: #666;
        }

        .button-group-sttng {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-top: 20px;
        }

        .button-group-sttng button {
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        .cancel-btn-sttng {
            background-color: transparent;
            border-bottom: 2px solid #ccc;
            color: #333;
        }

        .save-btn-sttng {
            background-color: #4f7198;
            color: #fff;
        }

        .small-text-sttng {
            font-size: 10px;
            color: #666;
            margin-top: 10px;
        }

        #changePassword {
        font-family: Arial, sans-serif;
        text-align: left; /* Aligns all text and elements to the left */
    }

    #changePassword h2 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    #changePassword label {
        display: block;
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 8px;
    }

    #changePassword input {
        display: block;
        width: 50%; /* Takes full width of the container */
        padding: 12px; /* Adds padding for a bigger input */
        font-size: 16px; /* Larger font size */
        margin-bottom: 20px; /* Space between inputs */
        border: 2px solid #ccc; /* Adds a border */
        border-radius: 8px; /* Rounds the corners */
        box-sizing: border-box; /* Prevents padding from increasing total width */
        transition: border-color 0.3s ease-in-out; /* Smooth border transition */
    }

    #changePassword input:focus {
        border-color: #007bff; /* Changes border color on focus */
        outline: none; /* Removes the default outline */
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Adds a glow effect */
    }

    #changePassword p.small-text-sttng {
        font-size: 12px;
        color: #555;
        margin-bottom: 20px;
    }

    .hidden {
        display: none;
    }

    </style>
    <script>
        function toggleSection(section) {
            document.getElementById('editPersonalDetails').classList.add('hidden');
            document.getElementById('changePassword').classList.add('hidden');
            document.getElementById(section).classList.remove('hidden');
        }

        function calculateAge() {
            const dob = document.getElementById('dob').value;
            if (dob) {
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                document.getElementById('age').value = age;
            }
        }
    </script>
</head>
<body>
    <div class="header-sttng">
        <button onclick="location.href='cebus.php'">&#8592;</button>
        <h1>Setting</h1>
    </div>
    <div class="container-sttng">
        <div class="left-side-sttng">
            <button onclick="toggleSection('editPersonalDetails')">Edit Personal Details</button>
            <button onclick="toggleSection('changePassword')">Change Password</button>
        </div>
        <hr class="vertical-hr-sttng">
        <div class="right-side-sttng">
            <div id="editPersonalDetails">
                <h2>Personal Details</h2>
                <form method="POST" action="">
                    <div class="input-group-sttng">
                        <input type="text" name="fName" placeholder="First Name" value="<?= htmlspecialchars($userDetails['fName']) ?>" required>
                        <input type="text" name="lName" placeholder="Last Name" value="<?= htmlspecialchars($userDetails['lName']) ?>" required>
                    </div>
                    <div class="input-group-sttng">
                        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($userDetails['email']) ?>" required>
                        <input type="tel" name="phone_num" placeholder="Phone Number" value="<?= htmlspecialchars($userDetails['phone_num']) ?>" required>
                    </div>
                    <div class="input-group-sttng">
                        <input type="text" name="address" placeholder="Address" value="<?= htmlspecialchars($userDetails['address']) ?>" required>
                        <input type="date" name="dob" value="<?= htmlspecialchars($userDetails['DOB']) ?>" onchange="calculateAge()" required>
                    </div>
                    <div class="input-group-sttng">
                        <input type="text" id="age" name="age" placeholder="Age" value="<?= htmlspecialchars($userDetails['age']) ?>" readonly required>
                        <input type="text" name="sex" placeholder="Sex" value="<?= htmlspecialchars($userDetails['sex']) ?>" required>
                    </div>
                    <div class="button-group-sttng">
                        <button type="button" class="cancel-btn-sttng" onclick="window.location.href='cebus.php'">Cancel</button>
                        <button type="submit" class="save-btn-sttng">Save Changes</button>
                    </div>
                </form>
            </div>

        
            <div id="changePassword" class="hidden">
                <h2>Change Password</h2>
                <form method="POST" action="change_password.php">
                <label>Enter Current Password</label>
                <input type="password" name="current_password" placeholder="Current Password" required>
                <label>Enter New Password</label>
                <input type="password" name="new_password" placeholder="New Password" required>
                <label>Re-enter New Password</label>
                <input type="password" name="confirm_password" placeholder="Re-enter New Password" required>
                <p class="small-text-sttng">*Your new password must be at least 8 characters long and include a combination of uppercase letters, lowercase letters, numbers, and special characters. It should not match your previous password.</p>
                <div class="button-group-sttng">
                    <button type="submit" name="change_password" class="save-btn-sttng">Save Changes</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
