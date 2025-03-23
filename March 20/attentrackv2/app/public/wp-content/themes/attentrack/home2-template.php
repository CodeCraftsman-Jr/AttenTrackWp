<?php
/*
Template Name: Home 2 Template
*/

// Remove login check to make this page publicly accessible

?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-image: url('<?php echo esc_url(get_template_directory_uri() . '/assets/images/containerbg.jpg'); ?>');
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        position: relative;
        overflow-x: hidden;
    }

    .container {
        position: relative;
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        padding: 30px;
        border-radius: 30px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        max-width: 1000px;
        width: 100%;
        overflow: hidden;
        z-index: 2;
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
        min-height: 85vh;
    }

    .content-wrapper {
        position: relative;
        z-index: 3;
        padding: 20px;
        max-width: 800px;
        width: 100%;
    }

    .model-container {
        width: 100%;
        max-width: 800px;
        height: 500px;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        margin: 10px auto;
        aspect-ratio: 4/3;
    }

    .sketchfab-embed-wrapper {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sketchfab-embed-wrapper iframe {
        width: 100%;
        height: 100%;
        border-radius: 20px;
    }

    h1 {
        color: #ffffff;
        font-size: 2.5em;
        margin-bottom: 15px;
        line-height: 1.3;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.2em;
        margin-bottom: 20px;
        line-height: 1.6;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    .button-wrapper {
        padding: 20px 0;
        width: 100%;
        position: relative;
        z-index: 5;
    }

    .start-button {
        display: inline-block;
        padding: 18px 40px;
        background: linear-gradient(45deg, #FF416C 0%, #FF4B2B 100%);
        color: #ffffff;
        border: none;
        border-radius: 50px;
        font-size: 1.2em;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 10px 30px rgba(255, 65, 108, 0.3);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .start-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.2));
        transform: translateX(-100%);
        transition: transform 0.6s;
    }

    .start-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(255, 65, 108, 0.4);
    }

    .start-button:hover::before {
        transform: translateX(100%);
    }

    @media (max-width: 1024px) {
        .container {
            min-height: 80vh;
            padding: 20px;
            max-width: 900px;
        }

        .model-container {
            height: 450px;
        }
    }

    @media (max-width: 768px) {
        .container {
            min-height: 75vh;
            padding: 15px;
            gap: 15px;
        }

        h1 {
            font-size: 2em;
        }

        .subtitle {
            font-size: 1.1em;
        }

        .model-container {
            height: 350px;
        }

        .start-button {
            padding: 15px 30px;
            font-size: 1.1em;
        }
    }
</style>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

<div class="container">
    <div class="content-wrapper">
        <h1>Ready to Test Your Focus?</h1>
        <p class="subtitle">Welcome to our attention assessment platform. Let's begin your journey to better understanding your attention patterns.</p>
        
        <div class="model-container">
            <div class="sketchfab-embed-wrapper">
                <iframe title="Brain Activity Visualization" frameborder="0" allowfullscreen mozallowfullscreen="true" webkitallowfullscreen="true" allow="autoplay; fullscreen; xr-spatial-tracking" xr-spatial-tracking execution-while-out-of-viewport execution-while-not-rendered web-share src="https://sketchfab.com/models/2df234ff65b0483fb5b5e15e40efa65d/embed?autospin=1&autostart=1&preload=1&ui_theme=dark"> </iframe>
            </div>
        </div>

        <div class="button-wrapper">
            <a href="<?php echo esc_url(home_url('/patient-details-form')); ?>" class="start-button">Start Assessment</a>
        </div>
    </div>
</div>
