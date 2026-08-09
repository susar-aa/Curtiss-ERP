<?php
$f = 'app/Views/auth/login.php';
$c = file_get_contents($f);

// Update HTML rendering
$c = str_replace("onclick=\"selectUser('<?= htmlspecialchars(\$user->username) ?>', '<?= htmlspecialchars(\$user->role) ?>')\"", "onclick=\"selectUser('<?= htmlspecialchars(\$user->username) ?>', '<?= htmlspecialchars(\$user->full_name) ?>', '<?= htmlspecialchars(\$user->role) ?>')\"", $c);
$c = str_replace("<?= strtoupper(substr(\$user->username, 0, 1)) ?>", "<?= strtoupper(substr(\$user->full_name, 0, 1)) ?>", $c);
$c = str_replace("<div class=\"user-name\"><?= htmlspecialchars(\$user->username) ?></div>", "<div class=\"user-name\"><?= htmlspecialchars(\$user->full_name) ?></div>", $c);

// Update JS function definition
$js_old = "function selectUser(username, role) {
            formUsernameInput.value = username;
            selectedUsernameDisplay.textContent = username;
            selectedUserRole.textContent = role;
            selectedAvatar.textContent = username.charAt(0).toUpperCase();";
$js_new = "function selectUser(username, fullName, role) {
            formUsernameInput.value = username;
            selectedUsernameDisplay.textContent = fullName;
            selectedUserRole.textContent = role;
            selectedAvatar.textContent = fullName.charAt(0).toUpperCase();";
$c = str_replace($js_old, $js_new, $c);

// Update JS initialization
$js_init_old = "                let role = \"User\";
                const userObj = eligibleUsers.find(u => u.username === previouslySelectedUsername);
                if (userObj) role = userObj.role;
                
                selectUser(previouslySelectedUsername, role);";
$js_init_new = "                let role = \"User\";
                let fullName = previouslySelectedUsername;
                const userObj = eligibleUsers.find(u => u.username === previouslySelectedUsername);
                if (userObj) {
                    role = userObj.role;
                    fullName = userObj.full_name;
                }
                
                selectUser(previouslySelectedUsername, fullName, role);";
$c = str_replace($js_init_old, $js_init_new, $c);

file_put_contents($f, $c);
echo "login.php updated.";
