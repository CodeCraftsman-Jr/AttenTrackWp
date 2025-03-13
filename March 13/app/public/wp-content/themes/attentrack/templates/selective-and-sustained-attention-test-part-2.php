<?php
/**
 * Template Name: Selective and Sustained Attention Test Part 2
 */

 if (!is_user_logged_in()) {
     wp_redirect(wp_login_url(get_permalink()));
     exit;
 }
 ?>
 
 <!DOCTYPE html>
 <html <?php language_attributes(); ?>>
 <head>
     <meta charset="<?php bloginfo('charset'); ?>">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <?php wp_head(); ?>
     <style>
         body {
             font-family: 'Arial', sans-serif;
             text-align: center;
             background-size: cover;
             background-position: center;
             background-attachment: fixed;
             padding: 30px;
             margin: 0;
             display: flex;
             justify-content: center;
             flex-direction: column;
             align-items: center;
             min-height: 100vh;
             color: #fff;
         }
 
         h1 {
             color: #fff;
             font-size: 36px;
             text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
             margin-bottom: 20px;
         }
 
         #timer {
             font-size: 24px;
             font-weight: bold;
             margin-top: 10px;
         }
 
         #letter {
             font-size: 200px;
             color: black;
             min-width: 100px;
             min-height: 60px;
             display: flex;
             align-items: center;
             justify-content: center;
             position: relative;
             overflow: hidden;
         }
 
         #inputBox {
             font-size: 28px;
             padding: 10px;
             width: 130px;
             margin-top: 20px;
             border-radius: 5px;
             border: 2px solid #ccc;
             transition: border 0.3s ease;
             text-align: center;
         }
 
         #inputBox:focus {
             border-color: #40E0D0;
             outline: none;
             box-shadow: 0 0 10px rgba(64, 224, 208, 0.3);
         }
 
         #startButton {
             font-size: 24px;
             padding: 15px 30px;
             margin-top: 30px;
             cursor: pointer;
             background-color: #40E0D0;
             color: white;
             border: none;
             border-radius: 5px;
             box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
             transition: transform 0.2s ease, box-shadow 0.2s ease;
         }
 
         #startButton:hover {
             background-color: #40E0D0;
             transform: translateY(-2px);
             box-shadow: 0 7px 14px rgba(0, 0, 0, 0.15);
         }
 
         .log {
             position: absolute;
             top: 20px;
             left: 20px;
             font-size: 16px;
             background: rgba(0, 0, 0, 0.8);
             padding: 15px;
             border-radius: 5px;
             max-width: 250px;
         }
 
         #test-id {
             position: absolute;
             top: 20px;
             right: 20px;
             font-size: 18px;
             background: rgba(0, 0, 0, 0.6);
             color: white;
             padding: 5px 10px;
             border-radius: 5px;
         }
 
         .modal {
             display: none;
             position: fixed;
             z-index: 1;
             left: 0;
             top: 0;
             width: 100%;
             height: 100%;
             background-color: rgba(0, 0, 0, 0.6);
             overflow: auto;
         }
 
         .modal-content {
             background-color: #fff;
             margin: 15% auto;
             padding: 30px;
             border-radius: 10px;
             width: 80%;
             max-width: 500px;
             text-align: center;
             animation: modalSlideIn 0.3s ease;
         }
 
         @keyframes modalSlideIn {
             from {
                 transform: translateY(-100px);
                 opacity: 0;
             }
             to {
                 transform: translateY(0);
                 opacity: 1;
             }
         }
 
         .modal-header {
             font-size: 24px;
             font-weight: bold;
             margin-bottom: 20px;
         }
 
         .modal-body {
             font-size: 18px;
             margin-bottom: 20px;
             color: black;
         }
 
         .modal-button {
             background-color: #4CAF50;
             color: white;
             padding: 10px 20px;
             border: none;
             border-radius: 5px;
             font-size: 18px;
             cursor: pointer;
             transition: background-color 0.2s ease;
         }
 
         .modal-button:hover {
             background-color: #45a049;
         }
 
         @keyframes letterChange {
             0% {
                 transform: translateX(-100%) scale(0.5);
                 opacity: 0;
             }
             50% {
                 transform: translateX(0) scale(1.2);
                 opacity: 0.8;
             }
             100% {
                 transform: translateX(0) scale(1);
                 opacity: 1;
             }
         }
 
         .letter-change {
             animation: letterChange 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
         }
     </style>
 </head>
 <body>
 
     <h1 style="color:black">Selective And Sustained Attention Test</h1>
     
     <div id="test-area" class="position-relative">
         <div id="timer" class="test-timer" style="color:black;">Time Remaining: 80s</div>
         <div id="test-id"></div>
         <div id="letter" class="test-letter"></div>
         <input type="text" id="inputBox" class="test-input" maxlength="1" autocomplete="off">
         <button id="startButton" class="test-button">Start Test</button>
     </div>

     <!-- Modal -->
     <div class="modal" id="gameOverModal">
         <div class="modal-content">
             <div class="modal-header"></div>
             <div class="modal-body"></div>
             <button id="continueButton" class="modal-button">Continue to Next Phase</button>
         </div>
     </div>

     <script>
         console.log('Test Phase 2 script starting...');
         
         // Initialize variables
         let gameStarted = false;
         let gameEnded = false;
         let currentLetter = '';
         let startTime = 0;
         let timerInterval;
         let timeLeft = 80;
         let responses = [];
         let responseReceived = false;
         let inputLocked = false;
         let correctCount = 0;
         let incorrectCount = 0;
         let missedCount = 0;

         // Get DOM elements
         const letterElement = document.getElementById('letter');
         const inputBox = document.getElementById('inputBox');
         const startButton = document.getElementById('startButton');
         const timerElement = document.getElementById('timer');
         const modal = document.getElementById('gameOverModal');
         const modalHeader = modal.querySelector('.modal-header');
         const modalBody = modal.querySelector('.modal-body');
         const continueButton = document.getElementById('continueButton');

         // Get test ID from URL
         let testID;
         const urlParams = new URLSearchParams(window.location.search);
         testID = urlParams.get('test_id');
        
         if (!testID) {
             console.error('No test ID found in URL');
             alert('Error: No test ID found. Please start from part 1.');
             window.location.href = '<?php echo get_permalink(get_page_by_path("selective-and-sustained-attention-test-part-1")); ?>';
         } else {
             console.log('Using Test ID from URL:', testID);
         }

         // Show test ID for reference
         document.getElementById('test-id').textContent = 'Test ID: ' + testID;

         // Hide input box initially
         inputBox.style.display = 'none';

         // Update timer display
         function updateTimer() {
             if (!gameStarted || gameEnded) return;
             
             if (timeLeft <= 0) {
                 clearInterval(timerInterval);
                 gameEnded = true;
                 showModal();
                 return;
             }
             
             timerElement.textContent = `Time Remaining: ${timeLeft}s`;
             timeLeft--;
         }

         // Generate letter
         function generateLetter() {
             console.log('Generating new letter...');
             if (gameEnded) {
                 console.log('Game ended, not generating more letters');
                 return;
             }

             // Check if previous letter was 'p' and no response was received
             if (currentLetter === 'p' && !responseReceived) {
                 console.log('Missed p response');
                 missedCount++;
                 document.getElementById('unattemptedCount').textContent = missedCount;
                 responses.push({
                     letter: currentLetter,
                     response: '',
                     correct: false,
                     reactionTime: 2000,
                     missed: true
                 });
             }

             responseReceived = false;
             const letters = ['b', 'd', 'p', 'q', 'r'];
             const randomIndex = Math.floor(Math.random() * letters.length);
             currentLetter = letters[randomIndex];
             console.log('New letter:', currentLetter);

             letterElement.textContent = currentLetter;
             letterElement.classList.add('letter-change');
             startTime = Date.now();

             setTimeout(() => {
                 letterElement.classList.remove('letter-change');
             }, 500);

             setTimeout(generateLetter, 2000);
         }

         // Start game
         function startGame() {
             console.log('Starting game...');
             if (!gameStarted) {
                 gameStarted = true;
                 
                 // Show input box and hide start button
                 inputBox.style.display = 'block';
                 startButton.style.display = 'none';
                 
                 inputBox.value = '';
                 inputBox.focus();
                 
                 // Start letter generation
                 generateLetter();
                 
                 // Start timer
                 timerInterval = setInterval(updateTimer, 1000);
             }
         }

         // Handle input
         inputBox.addEventListener('input', function(e) {
             if (!gameStarted || gameEnded || inputLocked) {
                 console.log('Input ignored - game not in correct state');
                 return;
             }

             const userInput = e.target.value.toLowerCase();
             console.log('Input received:', userInput);
             
             if (userInput.length > 0) {
                 console.log('Processing input:', userInput, 'Current letter:', currentLetter);
                 responseReceived = true;
                 inputLocked = true;

                 const reactionTime = Date.now() - startTime;
                 console.log('Reaction time:', reactionTime, 'ms');

                 if (currentLetter === 'p') {
                     if (userInput === 'p') {
                         console.log('Correct response to p');
                         correctCount++;
                         document.getElementById('correctCount').textContent = correctCount;
                     } else {
                         console.log('Incorrect response to p');
                         incorrectCount++;
                         document.getElementById('wrongCount').textContent = incorrectCount;
                     }
                 } else if (userInput === 'p') {
                     console.log('False alarm - p pressed when not shown');
                     incorrectCount++;
                     document.getElementById('wrongCount').textContent = incorrectCount;
                 }

                 responses.push({
                     letter: currentLetter,
                     response: userInput,
                     correct: (currentLetter === 'p' && userInput === 'p') || 
                             (currentLetter !== 'p' && userInput !== 'p'),
                     reactionTime: reactionTime,
                     missed: false
                 });

                 // Clear input and unlock for next letter
                 inputBox.value = '';
                 inputLocked = false;
             }
         });

         // Save results to database
         function saveResults() {
            console.log('Saving Phase 2 results...');
            let totalResponses = responses.length;
            let correctResponses = responses.filter(r => r.correct).length;
            let accuracy = (correctResponses / totalResponses) * 100;
            let avgReactionTime = responses.reduce((sum, r) => sum + r.reactionTime, 0) / totalResponses;
            let missedCount = responses.filter(r => r.missed).length;
            let falseAlarms = responses.filter(r => !r.correct && !r.missed).length;

            // Calculate score based on accuracy and reaction time
            let score = Math.round((accuracy * (2000 - avgReactionTime))/20);
            if (score < 0) score = 0;

            jQuery.ajax({
                url: attentrack_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_test_results',
                    nonce: attentrack_ajax.nonce,
                    test_id: testID,
                    test_phase: 2,
                    score: score,
                    accuracy: accuracy,
                    reaction_time: avgReactionTime / 1000,
                    missed_responses: missedCount,
                    false_alarms: falseAlarms,
                    responses: JSON.stringify(responses)
                },
                success: function(response) {
                    if (response.success) {
                        console.log('Results saved successfully:', response.data);
                        // Pass test ID to next part
                        window.location.href = '<?php echo get_permalink(get_page_by_path("selective-and-sustained-attention-test-part-3")); ?>?test_id=' + testID;
                    } else {
                        console.error('Failed to save results:', response.data?.message || 'Unknown error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                }
            });
        }

         // Show game over modal
         function showModal() {
             let totalResponses = responses.length;
             let correctResponses = responses.filter(r => r.correct).length;
             let accuracy = (correctResponses / totalResponses) * 100;
             let avgReactionTime = responses.reduce((sum, r) => sum + r.reactionTime, 0) / totalResponses;
             let missedCount = responses.filter(r => r.missed).length;
             let falseAlarms = responses.filter(r => !r.correct && !r.missed).length;

             saveResults();

             modalHeader.textContent = 'Game Over!';
             modalBody.innerHTML = `
                 Time's up! Here are your results:<br><br>
                 Total Responses: ${totalResponses}<br>
                 Correct Responses: ${correctResponses}<br>
                 Accuracy: ${accuracy.toFixed(1)}%<br>
                 Average Reaction Time: ${(avgReactionTime / 1000).toFixed(3)}s<br>
                 Missed Responses: ${missedCount}<br>
                 False Alarms: ${falseAlarms}
             `;
             modal.style.display = 'block';
         }

         // Add event listeners
         startButton.addEventListener('click', startGame);
         continueButton.addEventListener('click', function() {
             window.location.href = '<?php echo get_permalink(get_page_by_path('selective-and-sustained-attention-test-part-3')); ?>';
         });
     </script>
 </body>
 </html>