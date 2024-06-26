<?php
// ini_set('display_errors', 1);
session_start();
require_once __DIR__ . './includes/db_connect.php';
include_once __DIR__ . '/includes/html_start.php';

if (!isset($_SESSION['session_id'])) {
    $_SESSION['danger'] = "Reserved access!";
    header('Location: index.php');
    die();
};

$update = $conn->prepare('SELECT * FROM users WHERE id = ?');
$update->execute([$_GET["id"]]);

$row = $update->fetch();

$usernameErr = $emailErr = "";

if (isset($_POST['update'])) {
    $user_id = $_POST['id'];
    $username = $_POST['username'] ?? "";
    $email = $_POST['email'] ?? "";
    $city = $_POST['city'] ?? "";
    $gender = $_POST['gender'] ?? "";
    $address = $_POST['address'] ?? "";

    // validation username
    if (empty($_POST["username"])) {
        $usernameErr = "Username is required";
    } else {
        $username = test_input($_POST["username"]);
    }

    // validation email
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }

    // update
    if (empty($usernameErr) && empty($emailErr)) {
        try {
            $query = "UPDATE users SET username=:username, email=:email, city=:city, gender=:gender, address=:address
            WHERE id=:user_id";

            $statement = $conn->prepare($query);
            $data = [
                ':username' => $username,
                ':email' => $email,
                ':city' => $city,
                ':gender' => $gender,
                ':address' => $address,
                ':user_id' => $user_id
            ];

            $query_execute = $statement->execute($data);

            if ($query_execute) {
                $_SESSION['warning'] = "Updated Successfully";
                header('Location: dashboard.php');
                exit();
            } else {
                $_SESSION['danger'] = "OPS! Something wrong";
                header('Location: update_profile.php');
                exit();
            }
        } catch (PDOException $e) {
            echo "My Error Type:" . $e->getMessage();
        }
    }
}

// The test_input() function is used to filter and clean the data sent by the form, to avoid possible script and HTML injection attacks.
function test_input($data)
{
    $data = trim($data); //for empty whitespace
    $data = stripslashes($data); //unquotes a quoted string
    $data = htmlspecialchars($data); //convert special characters to HTML entries
    return $data;
}
?>

<!-- HTML ----------------------------------------------------------------------------------------------->

<body class="body-dash" <div class="container vh-100">
    <div class="row d-flex justify-content-center align-items-center vh-100">
        <div class="col col-md-6 col-lg-8 bg-light rounded">

            <div class="p-5 box">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3>Dear <?= $row['username'] ?></h3>

                        <?php include __DIR__ . './includes/session_alert.php'; ?>

                    </div>
                    <div class="d-flex">
                        <a href="./index.php">
                            <h3><i class="bi bi-house"></i></h3>
                        </a>
                        <a href="./dashboard.php">
                            <h3><i class="bi bi-person-circle ms-2"></i></h3>
                        </a>
                    </div>
                </div>

                <p>UPDATE PROFILE INFORMATION</p>

                <form action='' method="post" novalidate>
                    <table class="table">
                        <tbody class="table-text">
                            <tr class="d-none">
                                <td>
                                    <input name="id" value="<?= $row['id'] ?>" />
                                </td>
                            </tr>
                            <tr>
                                <td class="col-3">Username</td>
                                <td>
                                    <input type="text" class="w-100 input is-invalid" id="validateUsername" aria-describedby="validateUsernameFeedback" value="<?= $row['username'] ?>" name="username" />
                                    <div id="validateUsernameFeedback" class="invalid-feedback">
                                        <?php echo $usernameErr; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Email</td>
                                <td>
                                    <input type="email" class="w-100 input is-invalid" id="validateEmail" aria-describedby="validateEmailFeedback" value="<?= $row['email'] ?>" name="email" />
                                    <div id="validateEmailFeedback" class="invalid-feedback">
                                        <?php echo $emailErr; ?>
                                    </div>
                                </td>
                            </tr>

                            <tr class="d-none">
                                <td>
                                    <input name="password" value="<?= $row['password'] ?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>City</td>
                                <td>
                                    <input type="text" class="w-100 input" value="<?= $row['city'] ?? '' ?>" name="city" />
                                </td>
                            </tr>
                            <tr>
                                <td>Gender</td>
                                <td>
                                    <input type="text" class="w-100 input" value="<?= $row['gender'] ?? '' ?>" name="gender" />
                                </td>
                            </tr>

                            <tr>
                                <td>Address</td>
                                <td>
                                    <input type="text" class="w-100" value="<?= $row['address'] ?? '' ?>" name="address" />
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- update information -->
                    <a href="./update_profile.php?id=<?= $row['id'] ?>">
                        <button class="btn btn-dash" type="submit" name="update">Submit </button>
                    </a>
                </form>

            </div>
        </div>
    </div>
    </div>



    <!-- close body -->
    <?php include_once __DIR__ . '/includes/html_end.php'; ?>