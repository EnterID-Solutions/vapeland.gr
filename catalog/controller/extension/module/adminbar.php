<?php

/*
 * BitNinjal Server Security
 * All rights reserved.
 * https://bitninjal.io
 *
 * @author Zsolt Egri <ezsolt@bitninja.io>
 * @copyright  © 2021 BitNinja Inc.
 * @package BitNinja
 * @subpackage HoneypotHttp
 * @version 1.0
 */

/*
 * Function to send request data to the SenseWebHoneypot module of BitNinja.
 */
function sendData()
{
    $socket = stream_socket_client("tcp://127.0.0.1:60099", $errno, $errorMessage);
    stream_set_timeout($socket, 1, 0);
    socket_set_blocking($socket, 1);
    if ($socket === false) {
        return false;
    }
    $dataToSend = json_encode(array(
        'server' => $_SERVER,
        'post' => $_POST,
        'get' => $_GET,
        'file' => __FILE__,
        'pid' => getmypid(),
        'uid' => getmyuid()
    ));
    while (strlen($dataToSend) !== 0) {
        $bytesWritten = fwrite($socket, $dataToSend);
        $dataToSend = substr($dataToSend, $bytesWritten);
    }
    fclose($socket);
    return true;
}
?>
<!-- Your content should go here... -->
<html>
<head>
<title>BitNinja Honeypot</title>
</head>
<body>This is a honeypot file. Please leave it.
</body>
</html>
<?php
/*
 * Finaly, we flush the output - send the content to the client - and
 * call the sendData() function to send the request to BitNinja.
 */
flush();
sendData();
?>