<?php
/*
Template Name: Selection Page 2 Template
*/

?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Arial', sans-serif;
        min-height: 100vh;
        background: linear-gradient(120deg, #d4fc79, #96e6a1);
        padding: 20px;
        color: #2c3e50;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 30px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    header {
        text-align: center;
        margin-bottom: 40px;
        padding: 20px;
        border-bottom: 2px solid rgba(44, 62, 80, 0.1);
    }

    h1 {
        font-size: 2.8em;
        color: #2c3e50;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .subtitle {
        font-size: 1.2em;
        color: #34495e;
        margin-bottom: 30px;
    }

    .grid-container {
        display: flex;
        gap: 20px;
        overflow-x: auto;
        padding: 20px 0;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }

    .grid-item {
        flex: 0 0 300px;
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(44, 62, 80, 0.1);
        scroll-snap-align: start;
        position: relative;
    }

    .grid-item.disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .grid-item.disabled::after {
        content: attr(data-message);
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 15px;
        border-radius: 8px;
        font-size: 0.9em;
        text-align: center;
        width: 80%;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        z-index: 2;
    }

    .grid-item.disabled:hover::after {
        opacity: 1;
    }

    .grid-item img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        border-bottom: 4px solid #3498db;
    }

    .content {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: linear-gradient(145deg, #ff6ec7, #ff9a8b);
    }

    .grid-item h2 {
        color: #fff;
        font-size: 1.8em;
        margin-bottom: 15px;
        font-weight: 600;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    .grid-item p {
        color: #fff;
        font-size: 1.1em;
        line-height: 1.6;
        margin-bottom: 20px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
    }

    .grid-item button {
        background: rgba(255, 255, 255, 0.9);
        color: #2c3e50;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-size: 1.1em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        align-self: center;
        margin-top: auto;
    }

    .grid-item button:hover {
        background: #fff;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
    }

    .modal-content {
        position: relative;
        background: white;
        margin: 15% auto;
        padding: 30px;
        width: 90%;
        max-width: 500px;
        border-radius: 15px;
        box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        font-size: 1.8em;
        color: #2c3e50;
        margin-bottom: 20px;
        text-align: center;
    }

    .modal-body {
        font-size: 1.1em;
        line-height: 1.6;
        color: #34495e;
        margin-bottom: 25px;
        text-align: center;
    }

    .modal-button {
        background: linear-gradient(145deg, #ff6ec7, #ff9a8b);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 8px;
        font-size: 1.1em;
        cursor: pointer;
        transition: all 0.3s ease;
        display: block;
        margin: 0 auto;
    }

    .modal-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .close {
        position: absolute;
        right: 20px;
        top: 10px;
        font-size: 28px;
        font-weight: bold;
        color: #aaa;
        cursor: pointer;
    }

    .close:hover {
        color: #2c3e50;
    }

    @media (max-width: 768px) {
        .grid-container {
            flex-direction: column;
            align-items: center;
        }

        .grid-item {
            flex: 0 0 auto;
            width: 100%;
            max-width: 300px;
        }
    }
</style>

<div class="container">
    <header>
        <h1>Attention Assessment Tests</h1>
        <p class="subtitle">Choose a test to begin your assessment</p>
    </header>

    <div class="grid-container">
        <div class="grid-item" id="test1" onclick="test1Clicked()">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/test1.jpeg" alt="Selective Attention Test">
            <div class="content">
                <h2>Selective Attention</h2>
                <p>Test your ability to focus on specific information while ignoring distractions.</p>
                <a href="<?php echo esc_url(home_url('/selective-attention-test')); ?>"><button>Start Test</button></a>
            </div>
        </div>

        <div class="grid-item" id="test2" onclick="test2Clicked()">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/test1.jpeg" alt="Sustained Attention Test">
            <div class="content">
                <h2>Selective and Sustained Attention</h2>
                <p>Measure your ability to maintain focus over an extended period.</p>
                <a href="<?php echo esc_url(home_url('/selective-attention-test-extended')); ?>"><button>Start Test</button></a>
            </div>
        </div>

        <div class="grid-item" id="test3" onclick="test3Clicked()">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/test1.jpeg" alt="Alternative Attention Test">
            <div class="content">
                <h2>Alternative Attention</h2>
                <p>Evaluate your ability to switch focus between different tasks.</p>
                <a href="<?php echo esc_url(home_url('/alternative-attention-test')); ?>"><button>Start Test</button></a>
            </div>
        </div>

        <div class="grid-item" id="test4" onclick="test4Clicked()">
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/test1.jpeg" alt="Dividend Attention Test">
            <div class="content">
                <h2>Dividend Attention</h2>
                <p>Test your ability to process multiple information streams simultaneously.</p>
                <a href="<?php echo esc_url(home_url('/divided-attention-test')); ?>"><button>Start Test</button></a>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="instructionModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="instructionTitle" class="modal-header"></h2>
        <p id="instructionDescription" class="modal-body"></p>
        <button id="startTestButton" class="modal-button">Start Test</button>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    function updateTestStates() {
        // Get test progress from localStorage
        const testProgress = JSON.parse(localStorage.getItem('testProgress') || '{}');
        
        // Update each test's state
        for (let i = 1; i <= 4; i++) {
            const test = document.getElementById(`test${i}`);
            if (testProgress[`test${i}`] === 'completed') {
                test.classList.add('disabled');
                test.setAttribute('data-message', 'Test completed');
            }
        }
    }

    function showInstructionCard(title, description, redirectUrl) {
        console.log('showInstructionCard called with:', {
            title: title,
            description: description,
            redirectUrl: redirectUrl
        });

        // Get modal elements
        const modal = document.getElementById('instructionModal');
        const titleElement = document.getElementById('instructionTitle');
        const descriptionElement = document.getElementById('instructionDescription');
        const startButton = document.getElementById('startTestButton');

        if (!modal || !titleElement || !descriptionElement || !startButton) {
            console.error('Modal elements not found:', {
                modal: !!modal,
                titleElement: !!titleElement,
                descriptionElement: !!descriptionElement,
                startButton: !!startButton
            });
            return;
        }

        // Update modal content
        titleElement.textContent = title;
        descriptionElement.textContent = description;

        // Update start button click handler
        startButton.onclick = function() {
            console.log('Start button clicked, redirecting to:', redirectUrl);
            window.location.href = redirectUrl;
        };

        // Show modal
        modal.style.display = 'block';
        console.log('Modal displayed');
    }

    window.test2Clicked = function() {
        console.log('test2Clicked function called');
        if (!document.getElementById('test2').classList.contains('disabled')) {
            console.log('Test2 is not disabled, proceeding with AJAX call');
            // First get a unique test ID
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'get_unique_test_id',
                    nonce: '<?php echo wp_create_nonce('attentrack_test_nonce'); ?>'
                },
                beforeSend: function() {
                    console.log('Sending AJAX request to get test ID');
                    console.log('AJAX URL:', '<?php echo admin_url('admin-ajax.php'); ?>');
                    console.log('Nonce:', '<?php echo wp_create_nonce('attentrack_test_nonce'); ?>');
                },
                success: function(response) {
                    console.log('AJAX response received:', response);
                    if (response.success) {
                        console.log('Successfully got test ID:', response.data.test_id);
                        // Get the page ID for our template
                        const targetUrl = '<?php 
                            $page = get_page_by_path('selective-attention-test-extended');
                            echo $page ? get_permalink($page->ID) : home_url("/selective-attention-test-extended");
                        ?>?test_id=' + response.data.test_id;
                        console.log('Redirecting to:', targetUrl);
                        // Show instruction card with the test ID
                        showInstructionCard(
                            'Selective and Sustained Attention Test',
                            'This test measures your ability to maintain focus over time. You will need to concentrate for about 10 minutes.',
                            targetUrl
                        );
                    } else {
                        console.error('Failed to get test ID. Response:', response);
                        alert('Error starting test. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error details:');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    alert('Error starting test. Please try again.');
                }
            });
        } else {
            console.log('Test2 is disabled, click ignored');
        }
    };

    // Initialize test states
    updateTestStates();

    // Close modal functionality
    document.querySelector('.close').onclick = function() {
        document.getElementById('instructionModal').style.display = 'none';
    };

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('instructionModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };
});
</script>
