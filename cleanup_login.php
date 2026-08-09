<?php
$f = 'app/Views/auth/login.php';
$c = file_get_contents($f);

// 1. Remove the duplicate .user-card block that has the comment "/* width is set by..."
$c = str_replace(
"        .user-card {\n            background: var(--card-bg);\n            border: 1px solid var(--card-border);\n            border-radius: 20px;\n            padding: 32px 20px 24px;\n            display: flex; flex-direction: column; align-items: center;\n            text-align: center; cursor: pointer;\n            position: relative; overflow: hidden;\n            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);\n            box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 0 0 1px rgba(255,255,255,0.6) inset;\n            opacity: 0; transform: translateY(30px) scale(0.95);\n            transition: transform 0.25s cubic-bezier(0.34,1.56,0.64,1),\n                        box-shadow 0.25s ease, border-color 0.25s ease,\n                        background 0.25s ease;\n            /* width is set by .user-card flex rule above */\n        }",
"",
$c
);

// 2. Remove JS references to taglineText
$c = str_replace("const taglineText    = document.getElementById('taglineText');", "", $c);
$c = str_replace("        taglineText.textContent = 'Enter your password to authorize';", "", $c);
$c = str_replace("        taglineText.textContent = 'Identify yourself to continue';", "", $c);

file_put_contents($f, $c);
echo "Cleaned up.";
