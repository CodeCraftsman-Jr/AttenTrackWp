<?php
/*
Template Name: Comp 3 Template
*/

get_header();
?>

<style>
    body {
        background: linear-gradient(rgba(38, 38, 38, 0.8), rgba(34, 34, 34, 0.8)), url('https://img.freepik.com/free-vector/abstract-geometric-hexagonal-medical_1017-15002.jpg?t=st=1730651464~exp=1730655064~hmac=7c36d98b98edd85860e151c04efd959f0dfc3278d86379e634e337136298bb91&w=900');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        font-family: 'Roboto', sans-serif;
        color: #ffffff;
        overflow-x: hidden;
    }

    .section {
        padding: 50px 30px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        margin: 30px 0;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        backdrop-filter: blur(5px);
        transition: transform 0.3s ease;
    }

    .section:hover {
        transform: translateY(-5px);
    }

    h1 {
        font-family: 'Karla', sans-serif;
        font-size: 2.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
        background: linear-gradient(45deg, #ffdd57, #ff8c00);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    h2 {
        font-family: 'Karla', sans-serif;
        color: #ffdd57;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        margin-bottom: 20px;
    }

    p {
        font-size: 1.1rem;
        line-height: 1.8;
        opacity: 0.9;
    }

    .img-container {
        position: relative;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .img-container img {
        width: 100%;
        height: auto;
        transition: transform 0.3s ease;
    }

    .img-container:hover img {
        transform: scale(1.05);
    }

    .video-container {
        position: relative;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        margin: 30px 0;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    @media (max-width: 768px) {
        .section {
            margin: 15px 5px;
            padding: 30px 15px;
        }

        h1 {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.5rem;
        }
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="container" style="margin-top: 100px;">
    <div class="section">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="img-container">
                    <img src="https://static.vecteezy.com/system/resources/previews/044/267/382/non_2x/cartoon-medical-or-health-checkup-concept-illustration-vector.jpg" alt="Medical Illustration" class="img-fluid">
                </div>
            </div>
            <div class="col-lg-6">
                <h1>Alternative Attention</h1>
                <h2>Procedure Overview</h2>
                <p>Every Alphabet from A to Z will be shown in screen with a number representing them.
For Example: Below this __, blank line, if 8 is mentioned then you should write H in the blank space.
You can choose the response only once, correction is not allowed.
You have to attempt sequentially, do not skip any blank lines.
Maximum 50 blanks lines will be displayed.
Total time 1 minute</p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2 class="text-center mb-4">Watch Tutorial Video</h2>
        <div class="video-container">
            <iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/zyjTTUw6Bqc?si=339jwr8KyoQlMmSd" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
        </div>
    </div>
</div>

<?php
get_footer();
?>
