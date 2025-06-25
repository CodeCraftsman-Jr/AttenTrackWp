<?php
/*
Template Name: Debug Image
*/

get_header();

$background_image_url = get_template_directory_uri() . '/assets/images/containerbg.jpg';
?>

<div class="container py-5">
    <h1>Debug Image URLs</h1>
    
    <h3>Generated URL:</h3>
    <p><code><?php echo esc_html($background_image_url); ?></code></p>
    
    <h3>Test Direct Image Access:</h3>
    <img src="<?php echo esc_url($background_image_url); ?>" alt="Test Image" style="max-width: 300px; border: 2px solid red;">
    
    <h3>Test Background Image:</h3>
    <div style="width: 300px; height: 200px; background-image: url('<?php echo esc_url($background_image_url); ?>'); background-size: cover; background-position: center; border: 2px solid blue;">
        <p style="color: white; text-align: center; padding-top: 80px;">Background Test</p>
    </div>
    
    <h3>WordPress Info:</h3>
    <p>Home URL: <code><?php echo esc_html(home_url()); ?></code></p>
    <p>Site URL: <code><?php echo esc_html(site_url()); ?></code></p>
    <p>Template Directory URI: <code><?php echo esc_html(get_template_directory_uri()); ?></code></p>
    <p>Template Directory: <code><?php echo esc_html(get_template_directory()); ?></code></p>
    
    <h3>File System Check:</h3>
    <p>File exists: <?php echo file_exists(get_template_directory() . '/assets/images/containerbg.jpg') ? 'YES' : 'NO'; ?></p>
    <p>File size: <?php echo file_exists(get_template_directory() . '/assets/images/containerbg.jpg') ? filesize(get_template_directory() . '/assets/images/containerbg.jpg') . ' bytes' : 'N/A'; ?></p>
</div>

<?php get_footer(); ?>
