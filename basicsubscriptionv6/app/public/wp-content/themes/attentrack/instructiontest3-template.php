<?php
/*
Template Name: Instruction Test 3 Template
*/

// get_header();
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
        <li>You will see a sequence of colour and an audio sound saying 'Green/Red/yellow/violet/blue/orange/indigo'</li>
        <li>You should click the colour as you hear in audio</li>
        <li>Sequence of colour will be displayed for 2 seconds</li>
        <li>You can choose the response only once</li>
        <li>Total duration is 1 minute</li>
    </ul>
    <a href="<?php echo esc_url(home_url('/test-3')); ?>" class="start-test-btn">Start Test</a>
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All rights reserved.</p>
    </div>
</div>

<?php
// get_footer();
?>
