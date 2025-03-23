<?php
/*
Template Name: Patient Details Form
*/

?>

<style>
    body {
        background-image: url('https://static.vecteezy.com/system/resources/previews/045/898/520/large_2x/health-care-and-science-icon-pattern-medical-innovation-concept-background-design-vector.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 20px;
        color: #333;
    }
    .container {
        max-width: 700px;
        margin: auto;
        background: rgba(255, 255, 255, 0.65);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        transition: box-shadow 0.3s;
        padding: 30px;
        border-radius: 8px;
        margin-top: 50px;
        margin-bottom: 50px;
    }
    .container:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
    }
    h2 {
        text-align: center;
        color: #0784eb;
        margin-bottom: 20px;
        font-size: 24px;
        border-bottom: 2px solid #0784eb;
        padding-bottom: 10px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        color: #555;
        font-weight: bold;
    }
    .required {
        color: red;
        margin-left: 8px;
    }
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    textarea,
    select {
        width: 100%;
        padding: 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
        transition: border 0.3s;
        background-color: #fdfdfd;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border: 1px solid #0784eb;
        outline: none;
        background-color: #e9f5e9;
    }
    textarea {
        resize: vertical;
    }
    .submit-btn {
        background-color: #0784eb;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        transition: background-color 0.3s;
    }
    .submit-btn:hover {
        background-color: #0784eb;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.7);
        backdrop-filter: blur(5px);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        text-align: center;
        border-radius: 8px;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
    @media (max-width: 600px) {
        .container {
            padding: 20px;
        }
        h2 {
            font-size: 20px;
        }
    }
</style>

<div class="container">
    <h2>Patient Details Form</h2>
    <form id="patientForm">
        <div class="form-group">
            <label for="patientId">Patient ID</label>
            <input type="text" id="patientId" name="patientId" required>
        </div>
        <div class="form-group">
            <label for="firstName">First Name<span class="required">*</span></label>
            <input type="text" id="firstName" name="firstName" required>
        </div>
        <div class="form-group">
            <label for="lastName">Last Name<span class="required">*</span></label>
            <input type="text" id="lastName" name="lastName" required>
        </div>
        <div class="form-group">
            <label for="age">Age<span class="required">*</span></label>
            <input type="number" id="age" name="age" min="0" max="120" required>
        </div>
        <div class="form-group">
            <label for="gender">Gender<span class="required">*</span></label>
            <select id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="email">Email<span class="required">*</span></label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number<span class="required">*</span></label>
            <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
        </div>
        <button type="submit">Submit</button>
    </form>
</div>

<!-- Success Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Success!</h3>
        <p>Patient details have been saved successfully.</p>
        <button id="nextTestButton">Proceed to Tests</button>
    </div>
</div>

<script>
document.getElementById('patientForm').addEventListener('submit', async function(event) {
    event.preventDefault();
    
    const formData = new FormData(this);
    const jsonData = {};
    formData.forEach((value, key) => {
        jsonData[key] = value;
    });

    try {
        // Store in localStorage for now since we're not using a database
        localStorage.setItem('patientDetails', JSON.stringify(jsonData));
        
        // Show success modal
        document.getElementById('successModal').style.display = 'block';
        
        // Handle "Proceed to Tests" button click
        document.getElementById('nextTestButton').addEventListener('click', function() {
            window.location.href = '<?php echo esc_url(home_url("/selectionpage2")); ?>';
        });
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to save patient details. Please try again.');
    }
});

// Close modal when clicking the close button or outside the modal
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('successModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const modal = document.getElementById('successModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});
</script>


