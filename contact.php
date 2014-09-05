<?php

require_once('functions.php');

$result = -1;
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!@isset($_POST['message']) || trim($_POST['message']) == "") {
    $msg = '<p class="err">Invalid submission. Please include a message.</p>' . "\n";
  } else {
    if (@isset($_POST['email']) && trim($_POST['email']) != "") {
      if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['email'])) {
        $msg = '<p class="err">The email address you provided is invalid.</p>' . "\n";
      } else {
        $result = send_message($_POST['message'],$_POST['email']);
      }
    } else {
      $result = send_message($_POST['message']);
    }
  }
}

if ($result === true) {
  $msg = '<p class="msg">Your message was sent.  Thanks!</p>' . "\n";
} elseif ($result === false) {
  $msg = '<p class="err">Something went wrong, and your message was not sent. Sorry...</p>' . "\n";
}

include('common_head.php'); ?>
  <script type="text/javascript">
    function load() { pngfix(document.getElementById('logo')); }
  </script>
  </head>
  <body onload="load()">

<?php include('common_menu.php'); ?>

  <div id="title">
  <h2>Contact me</h2>
  </div>

  <div id="info" class="full contact">
    <?= $msg ?>
    <p>Use this form to shoot me an email, should the need arise. Feature
    requests, problems, questions, bug reports, etc.; anything is welcome. Thanks...</p>
    <form action="" method="post">
      <div>
        <p>
          <label for="email">Your email address (optional):</label>
          <input type="text" style="width: 300px;" name="email" id="email"/>
        </p>
        <p>
          <label for="message">Your message:</label><br/>
          <textarea rows="9" cols="58" name="message" id="message"></textarea>
        </p>
        <p>
          <input type="submit" value="Send"/>
        </p>
      </div>
    </form>
  </div>

  </body>
</html>
