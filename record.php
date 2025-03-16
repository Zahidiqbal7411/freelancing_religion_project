

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Survey Results</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<style>

    /* Global Styles */
body {
  font-family: 'Roboto', sans-serif;
  background-color: #f4f6f9;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
}

.page {
  background-color: #ffffff;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 900px;
}

.page-title {
  font-size: 2.5rem;
  color: #333;
  text-align: center;
  margin-bottom: 40px;
}

h2 {
  font-size: 1.8rem;
  color: #333;
  margin-bottom: 15px;
}

/* Section Styles */
.user-responses, .survey-results {
  margin-bottom: 40px;
}

.question {
  margin-bottom: 20px;
}

ul {
  list-style: none;
  padding-left: 0;
}

li {
  padding: 8px;
  margin: 5px 0;
  background-color: #f9f9f9;
  border-radius: 5px;
  transition: background-color 0.3s;
}

li.selected {
  background-color: #e0ffe0;
}

li.highlight {
  background-color: #d3e4ff;
}

.badge {
  font-size: 0.9rem;
  color: green;
  font-weight: 500;
  margin-left: 10px;
}

.total-votes {
  font-size: 1.2rem;
  color: #555;
  margin-bottom: 20px;
}

.votes-count {
  color: #28a745;
  font-weight: bold;
}

/* Footer Styles */
.footer {
  text-align: center;
  margin-top: 40px;
}

.btn {
  display: inline-block;
  background-color: #28a745;
  color: #ffffff;
  padding: 12px 20px;
  border-radius: 5px;
  text-decoration: none;
  font-size: 1.1rem;
  transition: background-color 0.3s;
}

.btn:hover {
  background-color: #218838;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .page {
    padding: 20px;
  }

  .page-title {
    font-size: 2rem;
  }

  h2 {
    font-size: 1.6rem;
  }
}

</style>
<body>

  <div id="summaryPage" class="page">
    <h1 class="page-title">Survey - Your Responses & Results</h1>

    <!-- User Responses Section -->
    <section class="user-responses">
      <h2>Your Selected Responses</h2>
      <div class="responses-list">
        <?php if (!empty($questions)): ?>
          <?php foreach ($questions as $questionId => $question): ?>
            <div class="question">
              <p><strong>Question <?= htmlspecialchars($questionId) ?>:</strong></p>
              <ul>
                <?php foreach ($question['options'] as $option): ?>
                  <li class="<?= (!empty($userSurveyResults[$questionId]) && $userSurveyResults[$questionId] == $option['option_id']) ? 'selected' : '' ?>">
                    <?= htmlspecialchars($option['option_text']) ?>
                    <?php if (!empty($userSurveyResults[$questionId]) && $userSurveyResults[$questionId] == $option['option_id']): ?>
                      <span class="badge">🟢 Your Selection</span>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No survey responses yet.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Survey Results Section -->
    <section class="survey-results">
      <h2>Survey Results (All Responses)</h2>
      <p class="total-votes">Total Votes: <span class="votes-count"><?= $tot_abc ?></span></p>
      <div class="results-list">
        <?php if (!empty($questions)): ?>
          <?php foreach ($questions as $questionId => $question): ?>
            <div class="question">
              <p><strong>Question <?= htmlspecialchars($questionId) ?>: <?= htmlspecialchars($question['question_text']) ?></strong></p>
              <ul>
                <?php if (!empty($allSurveyResults[$questionId])): ?>
                  <?php foreach ($allSurveyResults[$questionId] as $option): ?>
                    <li class="<?= (!empty($userSurveyResults[$questionId]) && $userSurveyResults[$questionId] == $option['option_id']) ? 'highlight' : '' ?>">
                      <?= htmlspecialchars($option['option_text']) ?> - <?= $option['percentage'] ?>%
                      <?php if (!empty($userSurveyResults[$questionId]) && $userSurveyResults[$questionId] == $option['option_id']): ?>
                        <span class="badge">Your Selection</span>
                      <?php endif; ?>
                    </li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <li>No responses yet for this question.</li>
                <?php endif; ?>
              </ul>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No survey responses yet. Please participate to see results.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
      <p>Thank you for participating!</p>
      <p>For help, email <strong>support@biblesignshappening.com</strong> or text <strong>816-715-0590</strong></p>
      <a href="https://biblesignshappening.com/" class="btn">Go To Home Page</a>
    </footer>
  </div>

  <script src="scripts.js"></script>
<script>  document.addEventListener('DOMContentLoaded', function () {
  // Smooth scroll for internal page links
  const btnHomePage = document.querySelector('.btn');
  btnHomePage.addEventListener('click', function (e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
});
</script>

</body>
</html>
