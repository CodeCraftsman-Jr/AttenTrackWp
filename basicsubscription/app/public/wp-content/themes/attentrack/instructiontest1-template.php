<?php
/*
Template Name: Instruction Test 1 Template
*/

get_header();
?>

<style>
    body {
        font-family: 'Roboto', sans-serif;
        background: linear-gradient(to right, #6a11cb, #2575fc);
        margin: 0;
        padding: 20px;
        color: black;
        min-height: 100vh;
    }
    .container {
        max-width: 800px;
        margin: 50px auto;
        background: rgba(255, 255, 255, 0.9);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }
    h1 {
        color: #333;
        text-align: center;
        margin-bottom: 30px;
    }
    ul {
        line-height: 1.8;
        padding-left: 20px;
        list-style-type: none;
    }
    li {
        background: #f4f4f4;
        margin: 10px 0;
        padding: 15px;
        border-radius: 5px;
        transition: transform 0.3s;
        position: relative;
        padding-left: 35px;
    }
    li:before {
        content: '→';
        position: absolute;
        left: 15px;
        color: #6a11cb;
    }
    li:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
    .footer {
        margin-top: 30px;
        text-align: center;
        color: #777;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    .start-test-btn {
        display: block;
        width: 200px;
        margin: 30px auto 0;
        padding: 15px 30px;
        background: linear-gradient(to right, #6a11cb, #2575fc);
        color: white;
        text-align: center;
        border-radius: 25px;
        text-decoration: none;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .start-test-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

<div class="container">
    <h1>Instructions</h1>
    <p>Welcome to the demo application! Please follow the instructions below:</p>
    <ul>
        <li>The alphabet such as 'P,b,d,q,R' will appear on the computer screen</li>
        <li>Each of the mentioned alphabets will be displayed one at a time in computer screen for 1 second</li>
        <li>You have to click 'P' whenever the alphabet 'P' appears on the screen. Do not press any other key</li>
        <li>Totally 200 stimuli will appear. After 50 stimuli there will blank screen for 30 seconds, for 1 sets will be displayed</li>
        <li>Total time will be approximately 5 Minutes</li>
    </ul>
    <a href="<?php echo esc_url(home_url('/test-1')); ?>" class="start-test-btn">Start Test</a>
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All rights reserved.</p>
    </div>
</div>

<?php
get_footer();
?>
