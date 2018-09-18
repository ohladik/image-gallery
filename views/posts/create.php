<main class="form-container <?php echo $GLOBALS['theme'] ?>">
    <form
            class="form <?php echo $GLOBALS['theme'] ?>"
            id="new-post"
            enctype="multipart/form-data"
            action="index.php?controller=posts&action=create_submit"
            method="post"
            onsubmit="return validateForm()"
    >
        <span class="form-title <?php echo $GLOBALS['theme'] ?>">New post</span>
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <div class="form-legend-container <?php echo $GLOBALS['theme'] ?>">
            <span class="form-required-label">
                required field
            </span>
        </div>
        <div class="form-input-container">
            <label class="form-label required" for="description">Description</label>
            <input
                    class="form-input"
                    id="description"
                    name="description"
                    type="text"
                    required
                    maxlength="90"
                    pattern=".{1,90}"
                    title="At least 1 character is required. Maximum allowed is 90 characters."
                    value="<?php if(isset($error)){ xecho($_POST['description']); } ?>"
            />
            <span class="form-error" id="description-form-error">
            <?php
            //display description error if any
            if (isset($error['description'])) {
                echo $error['description'];
            }
            ?>
            </span>
        </div>
        <div class="form-input-container">
            <label class="form-label required" for="category">Category</label>
            <select class="form-input" id="category" name="category" required>
                <option value="">Select category</option>
                <?php
                foreach($categories as $category) {
                    $selected = '';
                    if(isset($error) && $category->id == $_POST['category']){
                        $selected = 'selected';
                    }
                    echo "<option value=\"{$category->id}\" $selected>{$category->name}</option>";
                }
                ?>
            </select>
            <span class="form-error" id="category-form-error">
            </span>
        </div>
        <div class="form-input-container">
            <label class="form-label required" for="image">Image</label>
            <input class="<?php echo $GLOBALS['theme'] ?>" type="file" name="picture" id="image" accept="image/*" required/>
            <span class="form-error" id="image-form-error">
            <?php
            //display image error if any
            if(isset($error['image'])){
                echo $error['image'];
            }
            ?>
            </span>
        </div>
        <div class="form-button-container">
            <button class="form-button" type="submit">Create</button>
        </div>
    </form>
</main>

<script>
    window.onload = function() {
        // get inputs which will be validated
        var descriptionInput = document.getElementById('description');
        var categoryInput = document.getElementById('category');
        var fileInput = document.getElementById('image');


        descriptionInput.addEventListener('blur', validateDescription, false);
        categoryInput.addEventListener('change', validateCategory, false);
        fileInput.addEventListener('change', validateImage, false);
    };

    function showErrors(errors) {
        errors.forEach(function(error) {
            document.getElementById(error.element).innerHTML = error.message;
        });
    }

    function clearError(elementId) {
        document.getElementById(elementId).innerHTML = '';
    }

    function validateDescription() {
        var errors = [];
        var description = document.getElementById('description').value;

        // validation
        if (description.length === 0) {
            errors.push({ element: 'description-form-error', message: 'Please provide a description.'})
        } else if (description.length > 90) {
            errors.push({ element: 'description-form-error', message: 'Description is too long.'})
        }

        if (errors.length === 0) {
            clearError('description-form-error');
        } else {
            showErrors(errors);
        }

        // false if any error => form is not submitted
        return errors.length === 0;
    }

    function validateCategory() {
        var errors = [];
        var category = document.getElementById('category').value;

        // validation
        if (category === '') {
            errors.push({ element: 'category-form-error', message: 'Please select category.'})
        }

        if (errors.length === 0) {
            clearError('category-form-error');
        } else {
            showErrors(errors);
        }

        // false if any error => form is not submitted
        return errors.length === 0;
    }

    function validateImage() {
        var errors = [];
        var imagePath = document.getElementById('image').value;
        var imageSize = document.getElementById("image").files[0].size;
        var validExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp'];

        // validation
        // check if a file was uploaded
        if (imagePath === '') {
            errors.push({ element: 'image-form-error', message: 'Please upload a file.' });
        } else {
            // check if file doesn't exceed size limit
            if (imageSize > 2000000) {
                errors.push({ element: 'image-form-error', message: 'Maximum allowed image size is 2MB.' });
            }
            // check if file has image extension
            var fileExtension = imagePath.substring(imagePath.lastIndexOf(".") + 1).toLowerCase();
            if (validExtensions.indexOf(fileExtension) === -1) {
                errors.push({ element: 'image-form-error', message: 'Allowed file formats are PNG, JPG, JPEG, GIF and BMP.' });
            }
        }

        if (errors.length === 0) {
            clearError('image-form-error');
        } else {
            showErrors(errors);
        }

        // false if any error => form is not submitted
        return errors.length === 0;
    }

    function validateForm() {
        // false if any error => form is not submitted
        return validateDescription() && validateCategory() && validateImage();
    }
</script>