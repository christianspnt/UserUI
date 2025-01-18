<?php
// Include the database connection file
session_start();
require('connect.php');

// Get the logged-in user's account ID from the session
$account_ID = $_SESSION['user_id']; 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bankName = $_POST['bank_name'] ?? null;
    $method_name = $_POST['method_name'] ?? null;
    $accountNumber = $_POST['account_number'] ?? null;
    $accountName = $_POST['account_name'] ?? null;
    $cvv = $_POST['cvv'] ?? null;
    $email = $_POST['email'] ?? null;

    // Insert into the payment_method table
    $query = "INSERT INTO payment_method (
        account_ID,  
        registered_name,
        bank_name, 
        method_name, 
        p_method_details, 
        cvv, 
        expiration_date, 
        registration_date, 
        is_default, 
        tap_card_balance, 
        status
    ) VALUES (
        :account_ID,
        :registered_name, 
        :bank_name,
        :method_name, 
        :p_method_details, 
        :cvv, 
        NULL, 
        CURDATE(), 
        0, 
        NULL, 
        'Active'
    )";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
    $stmt->bindParam(':registered_name', $accountName, PDO::PARAM_STR);
    $stmt->bindParam(':bank_name', $bank_name, PDO::PARAM_STR);
    $stmt->bindParam(':method_name', $method_name, PDO::PARAM_STR);
    $stmt->bindParam(':p_method_details', $accountNumber, PDO::PARAM_STR);
    $stmt->bindParam(':cvv', $cvv, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "<script>alert('Payment method added successfully!');</script>";
    } else {
        echo "<script>alert('Failed to add payment method.');</script>";
    }
}

// Retrieve linked payment methods
$query = "SELECT pm.bank_name, pm.method_name, pa.email, pm.p_method_details, pm.registered_name, pm.cvv
          FROM payment_method pm
          LEFT JOIN passenger_accounts pa ON pm.account_ID = pa.account_ID
          WHERE pm.account_ID = :account_ID AND pm.status = 'Active'";

$stmt = $conn->prepare($query);
$stmt->bindParam(':account_ID', $account_ID, PDO::PARAM_INT);
$stmt->execute();
$paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['id'])) {
    $method_ID = $_GET['id'];

    $query = "DELETE FROM payment_method WHERE method_ID = :method_ID";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':method_ID', $method_ID, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Payment method removed successfully!";
    } else {
        echo "Failed to remove payment method.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Settings</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: radial-gradient(circle at top left, #4f7198, #f9ce80, #4f7198);
            background-position: top left;
            background-size: cover;
            height: 100vh;
            overflow: hidden;
        }

        .header {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: white;
        }

        .header .back-arrow {
            background: none;
            border: none;
            font-size: 18px;
            color: white;
            cursor: pointer;
            margin-right: 10px;
        }

        .header .title {
            font-size: 20px;
            font-weight: bold;
        }

        .content {
            display: flex;
            flex-wrap: nowrap;
            padding: 20px;
            gap: 20px;
        }

        .left-div, .right-div {
            padding: 20px;
            background-color:transparent;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .left-div {
            flex: 0 0 38%;
        }

        .right-div {
            flex: 0 0 55%;
        }

        .left-div hr {
            border: 1px solid #ddd;
            margin: 20px 0;
        }

        .add-btn, .account-btn {
            display: block;
            width: 100%;
            padding: 13px;
            background-color: #4f7198;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-align: left;
            cursor: pointer;
            margin-bottom: 10px;
            position: relative;
        }

        .add-btn:hover, .account-btn:hover {
            background-color: #3e5a7d;
        }

        .account-btn .menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 10px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 13px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .account-btn:hover .menu {
            display: block;
        }

        .menu button {
            background: none;
            border: none;
            padding: 5px 10px;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .menu button:hover {
            background-color: #eee;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"] {
            width: 95%;
            padding: 13px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        input[readonly] {
            background-color: #f5f5f5;
        }

        .add-button {
            background-color: #4f7198;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .add-button:hover {
            background-color: #3e5a7d;
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="back-arrow" onclick="window.location.href='cebus.php'">&larr;</button>
        <span class="title">Payment Settings</span>
    </div>

    <div class="content">
        <!-- Left Div -->
        <div class="left-div">
            <button class="add-btn" onclick="showAddForm()" >Add Bank Account</button>
            <hr>
            <div id="account-buttons">
            <?php foreach ($paymentMethods as $method): ?>
                <button class="account-btn" 
                    data-bank-name="<?= htmlspecialchars($method['bank_name']) ?>" 
                    data-method-name="<?= htmlspecialchars($method['method_name']) ?>" 
                    data-account-number="<?= htmlspecialchars($method['p_method_details']) ?>" 
                    data-registered-name="<?= htmlspecialchars($method['registered_name']) ?>" 
                    data-cvv="<?= htmlspecialchars($method['cvv']) ?>" 
                    data-email="<?= htmlspecialchars($method['email']) ?>" 
                    onclick="showAccountDetails(this)">
                    <?= htmlspecialchars($method['registered_name']) ?> (<?= htmlspecialchars($method['method_name']) ?>)
                </button>

            <?php endforeach; ?>


            </div>
        </div>

        <!-- Right Div -->
        <div class="right-div">
            <h3 id="form-title">Linked Bank Account</h3>
            <form id="account-form" method="POST" onsubmit="return validateForm()">
                <label for="bank-name">Select Partner Banks</label>
                <input type="text" name="bank_name" id="bank-name" placeholder="Enter bank name" required>
                <label for="method_name">Input type of Card</label>
                <input type="text" name="method_name" id="method_name" placeholder="Enter Card Type(eg. Tap card, Debit card)" required>
                <label for="account-number">Account Number</label>
                <input type="text" name="account_number" id="account-number" placeholder="Enter account number" required>
                <label for="account-name">Account Name</label>
                <input type="text" name="account_name" id="account-name" placeholder="Enter account name" required>
                <label for="cvv">CVV</label>
                <input type="text" name="cvv" id="cvv" placeholder="Enter CVV" required>
                <label for="email">Email</label>
                <input type="text" name="email" id="email" placeholder="Enter email">
                <button type="submit" class="add-button">Add</button>
            </form>
        </div>
    </div>

    <script>
        const accountButtons = document.getElementById('account-buttons');
        const form = document.getElementById('account-form');
        let editingAccount = null;

        function showAddForm() {
            form.reset();
            form.querySelectorAll('input').forEach(input => input.readOnly = false);
            document.getElementById('form-title').textContent = 'Add Bank Account';
            editingAccount = null;
        }

        function addAccount(event) {
            event.preventDefault();

            const bankName = document.getElementById('bank-name').value;
            const accountNumber = document.getElementById('account-number').value;
            const accountName = document.getElementById('account-name').value;
            const cvv = document.getElementById('cvv').value;
            const email = document.getElementById('email').value;

            if (editingAccount) {
                // Update existing account
                editingAccount.dataset.bankName = bankName;
                editingAccount.dataset.methodName = methodName;
                editingAccount.dataset.accountNumber = accountNumber;
                editingAccount.dataset.registeredName = accountName;
                editingAccount.dataset.cvv = cvv;
                editingAccount.dataset.email = email;
                editingAccount.textContent = `${accountName} (${methodName})`;
                showAccountDetails(editingAccount);
                editingAccount = null;
                return;
            }

            const button = document.createElement('button');
            button.className = 'account-btn';
            button.dataset.bankName = bankName;
            button.dataset.accountNumber = accountNumber;
            button.dataset.accountName = accountName;
            button.dataset.cvv = cvv;
            button.dataset.email = email;
            button.textContent = {$accountName} ({$bankName});
            button.onclick = () => showAccountDetails(button);

            const menu = document.createElement('div');
            menu.className = 'menu';
            const editButton = document.createElement('button');
            editButton.textContent = 'Edit';
            editButton.onclick = () => editAccount(button);
            const removeButton = document.createElement('button');
            removeButton.textContent = 'Remove';
            removeButton.onclick = () => removeAccount(button);
            menu.appendChild(editButton);
            menu.appendChild(removeButton);

            button.appendChild(menu);
            accountButtons.appendChild(button);
            form.reset();
        }

        function showAccountDetails(button) {
            document.getElementById('bank-name').value = button.getAttribute('data-bank-name');
            document.getElementById('method_name').value = button.getAttribute('data-method-name');
            document.getElementById('account-number').value = button.getAttribute('data-account-number');
            document.getElementById('account-name').value = button.getAttribute('data-registered-name');
            document.getElementById('cvv').value = button.getAttribute('data-cvv');
            document.getElementById('email').value = button.getAttribute('data-email');
        }



        function editAccount(button) {
            const data = button.dataset;
            form.querySelector('#bank-name').value = data.bankName;
            form.querySelector('#account-number').value = data.accountNumber;
            form.querySelector('#account-name').value = data.accountName;
            form.querySelector('#cvv').value = data.cvv;
            document.getElementById('form-title').textContent = 'Edit Bank Account';
        }

        function removeAccount(id) {
            if (confirm('Are you sure you want to remove this account?')) {
                fetch('delete_payment_method.php?id=' + id, { method: 'GET' })
                    .then(response => response.text())
                    .then(data => {
                        alert(data);
                        location.reload();
                    });
            }
        }
    </script>

</body>
</html>