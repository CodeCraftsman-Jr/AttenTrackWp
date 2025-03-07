<?php
/*
Template Name: Comp 2 Template
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

    .navbar {
        background-color: rgba(25, 153, 252, 0.9) !important;
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .navbar-brand {
        font-family: 'Karla', sans-serif;
        font-weight: 700;
        color: #ffffff !important;
    }

    .nav-link {
        color: #ffffff !important;
        margin: 0 10px;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        color: #ffdd57 !important;
        transform: translateY(-2px);
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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="content">
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">About Test</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo home_url(); ?>"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo home_url('/about-app/'); ?>"><i class="fas fa-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo home_url('/contact-us/'); ?>"><i class="fas fa-phone"></i> Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container" style="margin-top: 100px;">
        <?php
        // Include the content from comp2.html
        include locate_template('comp2.html');
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php
get_footer();
?>
