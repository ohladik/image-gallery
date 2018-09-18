<main class="form-container <?php echo $GLOBALS['theme'] ?>">
    <form
            class="form onboard <?php echo $GLOBALS['theme'] ?>"
            action="index.php?controller=registration&action=registration_submit"
            method="post"
            onsubmit="return validateForm()"
    >
        <span class="form-title <?php echo $GLOBALS['theme'] ?>">Registration</span>
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <div class="form-legend-container <?php echo $GLOBALS['theme'] ?>">
            <span class="form-required-label">
                required field
            </span>
        </div>
        <div class="form-input-container">
            <label class="form-label required" for="email">Email</label>
            <input
                    class="form-input"
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                    pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$"
                    title="Invalid email address"
                    value="<?php if(isset($error)){ xecho($_POST['email']); } ?>"
            />
            <span class="form-error" id="email-form-error">
            <?php
            //display email error if any
            if(isset($error['email'])){
                echo($error['email']);
            }
            ?>
            </span>
        </div>
        <div class="form-input-container">
            <label class="form-label required" for="password">Password</label>
            <input
                    class="form-input"
                    id="password"
                    name="password"
                    type="password"
                    minlength="8"
                    maxlength="64"
                    required
                    pattern=".{8,64}"
                    title="At least 8 characters is required. Maximum allowed is 64 characters."
            />
            <span class="form-error" id="password-form-error">
            <?php
            //display password error if any
            if(isset($error['password'])){
                echo $error['password'];
            }
            ?>
            </span>
        </div>
        <div class="form-button-container">
            <button class="form-button" type="submit">Create account</button>
        </div>
    </form>
    <span class="form-onboard-link <?php echo $GLOBALS['theme'] ?>">Already a member? <a class="form-bottom-link" href="?controller=login&action=login">Log in</a></span>
</main>

<script>
    window.onload = function() {
        // get inputs which will be validated
        var emailInput = document.getElementById('email');
        var passwordInput = document.getElementById('password');


        emailInput.addEventListener('blur', validateEmail, false);
        passwordInput.addEventListener('blur', validatePassword, false);
    };

    function showErrors(errors) {
        errors.forEach(function(error) {
            document.getElementById(error.element).innerHTML = error.message;
        });
    }

    function clearError(elementId) {
        document.getElementById(elementId).innerHTML = '';
    }

    function validateEmail() {
        var errors = [];
        var email = document.getElementById('email').value;

        // validation
        // regex is simple, its purpose is only to notify users of errors like missing '@'
        var pattern = /\S+@\S+\.\S+/;
        if (!pattern.test(email)) {
            errors.push({ element: 'email-form-error', message: 'Please provide a valid email address.'})
        }

        if (errors.length === 0) {
            clearError('email-form-error');
        } else {
            showErrors(errors);
        }

        // false if any error => form is not submitted
        return errors.length === 0;
    }

    function validatePassword() {
        var errors = [];
        var password = document.getElementById('password').value;

        // validation
        if (password.length < 8) {
            errors.push({ element: 'password-form-error', message: 'Minimal required length is 8 characters.' });
        } else if (password.length > 64) {
            errors.push({ element: 'password-form-error', message: 'Maximal allowed length is 64 characters.' });
        }

        if (errors.length === 0) {
            clearError('password-form-error');
        } else {
            showErrors(errors);
        }

        // false if any error => form is not submitted
        return errors.length === 0;
    }

    function validateForm() {
        // false if any error => form is not submitted
        return validateEmail() && validatePassword();
    }
</script>