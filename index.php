<?php
session_start();

require_once('incs/conn.php');
if (!isset($_SESSION['id']) || $_SESSION['id'] == "") {
    header('location:login.php');
    exit;
}

$user_id = $_SESSION['id'];
$email = $_SESSION['email'];
$admin_role = $_SESSION['role'];

if (isset($_POST['update_survey']) && $_POST['update_survey'] == 'yes') {
    // Loop through each question and selected option
    foreach ($_POST['question_id'] as $questionCount => $selected_option_id) {
        $selected_option_id = intval($selected_option_id); // Sanitize the option_id

        // Prepare SQL to update the survey responses
        $sql_update = "UPDATE survey_results 
                SET option_id = ? 
                WHERE question_id = ? 
                AND user_id = ?";

        // Prepare statement
        if ($stmt = $conn->prepare($sql_update)) {
            // Bind parameters
            $stmt->bind_param("iii", $selected_option_id, $questionCount, $user_id);

            // Execute the statement
            $stmt->execute();

            // Check if the update was successful
            if ($stmt->affected_rows > 0) {
                // Successfully updated
            } else {
                // Handle if no rows were affected (no update made)
            }

            // Close the statement
            $stmt->close();
        } else {
            // Handle error in preparing the statement
            echo "Error in preparing the query: " . $conn->error;
        }
    }
    header("location:index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .dashboard-container {
            width: 80%;
            margin: 50px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            font-size: 30px;
            color: #333;
            margin-bottom: 20px;
        }

        .question {
            margin-bottom: 20px;
            font-size: 18px;
            color: #555;
        }

        .options {
            margin-left: 20px;
            display: flex;
            flex-direction: column;
        }

        .option-label {
            font-size: 16px;
            margin: 5px 0;
            cursor: pointer;
        }

        .option-label input {
            margin-right: 10px;
        }

        .button-container {
            margin-top: 30px;
            text-align: center;
        }

        .update-button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .update-button:hover {
            background-color: #45a049;
        }

        .submit-button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #008CBA;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .submit-button:hover {
            background-color: #007B9A;
        }

        .welcome-message {
            font-family: Arial, sans-serif;
            font-size: 1.5em;
            color: #333;
            padding: 20px;
        }

        .welcome-message span {
            font-weight: bold;
            color: #007BFF;
        }

        .welcome-message {
            display: flex;
            justify-content: center;
        }
    </style>
</head>

<body>

    <?php
    $sql = "SELECT name, last_name FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql);
    $stmt_user->bind_param("i", $user_id); // Bind the user ID parameter to the query
    $stmt_user->execute();
    $result = $stmt_user->get_result();

    // Check if a user was found
    if ($result->num_rows > 0) {
        // Output the logged-in user
        $row = $result->fetch_assoc();
        $name = $row['name'];
        $last_name = $row['last_name'];

        // Check if the last name exists
        if (!empty($last_name)) {
            // Display the welcome message with both first and last name
            echo '<div class="welcome-message">
          <div class="row" style="margin-left:280px;">
          <div class="col-md-10">               
           <p>Welcome, <span>' . htmlspecialchars($name) . ' ' . htmlspecialchars($last_name) . '</span>!</p>
          </div>
                <div class="col-md-2">
              <form action="incs/logout.php" method="post" style="margin-right:50px;">
             <button type="submit" name="logout" class="btn btn-danger" style="margin-left:160px; border-radius:5px;">Logout</button>
              </form>
              </div>
             </div>
          </div>';
        } else {
            // If no last name exists, just display the first name
            echo '<div class="welcome-message">
                <p>Welcome, <span>' . htmlspecialchars($name) . '</span>!</p>
              </div>';
        }
    } else {
        // In case no user is found, handle the case
        $user_name = "Guest"; // Default name when no user is found
    }

    // Fetch questions from the database
    $sql = "SELECT * FROM questions";
    $result = $conn->query($sql);

    $optionSql = "SELECT * FROM options";
    $optionResult = $conn->query($optionSql);
    $all_options = array();
    while ($option = $optionResult->fetch_assoc()) {
        $all_options[$option['question_id']][$option['option_id']] = $option['text'];
    }

    $result_query = "SELECT * FROM survey_results WHERE user_id = '".$user_id."' ";
    $surveyResult = $conn->query($result_query);

    // Check if the query executed successfully
    if (!$surveyResult) {
        die("Query failed: " . $conn->error); // This will show the specific error
    }

    // Initialize the result array
    $all_survey_result = array();

    // Fetch and store results
    while ($sur_result = $surveyResult->fetch_assoc()) {
        $all_survey_result[$sur_result['question_id']] = $sur_result['option_id'];
    }

    // Initialize a counter to dynamically generate question ids (q1, q2, etc.)
    $questionCount = 1;

    // Start outputting the HTML
    echo '
    <form action="" method="post">
    <div class="dashboard-container">
        <div class="header fw-bold">Question Dashboard</div>';

    // Loop through the questions and display them dynamically
    while ($row = $result->fetch_assoc()) {
        $question_id = $row['question_id'];
        echo '<div class="question">
            <p>' . $questionCount . '. ' . htmlspecialchars($row['questions']) . '</p>
            <div class="options">';

        // Fetch options for this question
        $optionCount = 1; // To track the number of options dynamically
        // Loop through the options and display them
        foreach ($all_options[$question_id] as $option_id => $option_text) {
            $checked = '';
            if (isset($all_survey_result[$question_id]) && $all_survey_result[$question_id] == $option_id) {
                $checked = 'checked';
            }
            echo '<label class="option-label">
                <input type="radio" name="question_id[' . $questionCount . ']" value="' . $option_id . '" ' . $checked . '> ' . $option_text . '
              </label>';
            $optionCount++;
        }

        echo '  </div>
        </div>';

        $questionCount++;
    }

    echo '  <div class="button-container">
            <button class="submit-button">Update Survey</button>
            <input type="hidden" name="update_survey" value="yes" />
        </div>
      </div>
      </form>
      ';

    $conn->close();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>

</html>
