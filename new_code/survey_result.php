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
$msg = '';
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



            // Check if the update was successful
            if ($stmt->execute()) {
                $msg =  "<p style='color:green;'>Thank you! Survey updated Successfully!</p>";
            } else {
                $msg =  "<p style='color:red;'>Sorry! Error Occur!</p>";
            }

            // Close the statement
            $stmt->close();
        } else {
            // Handle error in preparing the statement
            $msg =  "<p style='color:red;'>Error in preparing the query: " . $conn->error ."</p>";
        }
    }
}
else
{
    header('location:index.php');
    exit;
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
    $allSurveyResults = [];
    $sqlResults = "SELECT question_id,option_id,count(surver_result_id) as total_answers from survey_results where status = 1 group by question_id,option_id;
";
    
    $resultData = $conn->query($sqlResults);
    $specific_question = 0;
    if ($resultData->num_rows > 0) {
        while ($row = $resultData->fetch_assoc()) {
            $allSurveyResults[$row['question_id']][$row['option_id']] = $row['total_answers'];
            if($specific_question == 0) $specific_question = $row['question_id'];
        }
    }
    $total_votes = array_sum($allSurveyResults[$specific_question]);



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
    <div class="dashboard-container">
        <div class="header fw-bold">'.$msg.'Question Dashboard <br/> Total Votes: '.$total_votes.'</div>';

    // Loop through the questions and display them dynamically
    while ($row = $result->fetch_assoc()) {
        $question_id = $row['question_id'];
        echo '<div class="question">
            <p>' . $questionCount . '. ' . htmlspecialchars($row['questions']) . '</p>
            <ul class="options">';

        // Fetch options for this question
        $optionCount = 1; // To track the number of options dynamically
        // Loop through the options and display them
        foreach ($all_options[$question_id] as $option_id => $option_text) {
            $checked = '';
            if (isset($all_survey_result[$question_id]) && $all_survey_result[$question_id] == $option_id) {
                $checked = '<b> (Your Answer)</b>';
            }

            $percentage = ' (0.00%) ';
            $this_option_vote = isset($allSurveyResults[$question_id][$option_id]) ? $allSurveyResults[$question_id][$option_id] : 0;
            if($this_option_vote > 0 && $total_votes > 0) {
                $percentage = " (".round(($this_option_vote / $total_votes) * 100 , 2)."%) ";
            }


            echo '<li> ' . $option_text . $percentage . $checked . ' </li>';
            $optionCount++;
        }

        echo '  </ul>
        </div>';

        $questionCount++;
    }

    

    echo '  <div class="button-container">
    <a href="index.php" class="submit-button">Back to Edit form</a>
</div>
</div>
';



    $conn->close();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

</body>

</html>
