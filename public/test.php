<?php
// die(phpinfo());
error_reporting(E_ALL);
ini_set('display_errors', 1);

// if (!in_array(php_sapi_name(), ['cli', 'cli-server'])) {
//     header("Content-Type: text/plain;");
// }

// function myReadDir(string $address)
// {
//     $result = [];
//     dirCore($address, $result);
//     var_export($result);
// }

// function dirCore(string $address, array &$result = null)
// {
//     if (is_null($result)) {
//         $result = [];
//     }
//     $result[] = $address;
//     if (is_dir($address)) {
//         $list = scandir($address);
//         foreach ($list as $item) {
//             if (!in_array($item, ['.', '..'])) {
//                 dirCore($address . '/' . $item, $result);
//             }
//         }
//     }
// }
