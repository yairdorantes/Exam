<?php
// Set the appropriate content type for JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('mensaje' => 'Metodo no permitido'));
    exit();
}

$jsonData = file_get_contents('php://input');
$parsedData = json_decode($jsonData, true);

function validateInput($input, $type) {
    if ($type === 'date') {
        $dateFormat = 'Y-m-d';
        $parsedDate = \DateTime::createFromFormat($dateFormat, $input);
        if ($parsedDate === false) {
            return false; // Invalid date format
        }
        $errors = $parsedDate->getLastErrors();
        if ($errors !== false && $errors['error_count'] > 0) {
            return false; // Invalid date format
        }
        return $parsedDate->format($dateFormat) === $input;
    } elseif ($type === 'text') {
        $pattern = '/^[A-Za-z\s]+$/';
        return is_string($input) && preg_match($pattern, $input);
    } elseif ($type === 'numbers') {
        $pattern = '/^[0-9]+$/';
        return is_string($input) && preg_match($pattern, $input);
    } else {
        return false; // Invalid type
    }
}

function validateJSON($data){
        $isValid = true;
        foreach ($data as $key => $value) {
            if (empty($value)) {
                // echo "Key '$key' is empty.\n";
                $isValid = false;
                break;
            } 
        }
        if($isValid){
            $inputName  = isset($data["nombre"]) ? $data["nombre"] : null;
            $inputPhone = isset($data["telefono"]) ? $data["telefono"] : null;
            $inputDate = isset($data["fecha"]) ? $data["fecha"] : null;
            $inputGenre = isset($data["genero"]) ? $data["genero"] : null;
            $inputEdo = isset($data["EdoSolicitud"]) ? $data["EdoSolicitud"] : null;
            
            $isValid = (validateInput($inputName,'text')&&validateInput($inputPhone,'numbers')&&validateInput($inputDate,'date')&&validateInput($inputGenre,'text')&& validateInput($inputEdo ,'text') );
        }
        return $isValid;

}
$isValid = validateJSON($parsedData);

if (!$isValid) {
    http_response_code(400);
    echo json_encode(array('mensaje' => 'JSON no valido, revisa cada campo'));
    exit();
}

if ($parsedData === null) {
    http_response_code(400); // Bad Request
    echo json_encode(array('mensaje' => 'JSON no valido'));
    exit();
}
// Check if the Authorization header is present
if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    // Unauthorized, no token provided
    http_response_code(401);
    echo json_encode(array('mensaje' => 'Unauthorized'));
    exit();
}
// Get the Bearer Token
$authHeader = $_SERVER['HTTP_AUTHORIZATION'];
$token = str_replace('Bearer ', '', $authHeader);

// Replace this with your actual token validation logic
$validToken = 'UgovWflFf3LBUx';

if ($token !== $validToken) {
    // Unauthorized, invalid token
    http_response_code(401);
    echo json_encode(array('mensaje' => 'NO Autorizado'));
    exit();
}

$savePath = './Data/';
$filename = $savePath . 'DB.txt';

//********* ************ */


// Read the existing JSON array from the file, if it exists
$existingData = file_exists($filename) ? file_get_contents($filename) : '[]';

// Decode the existing JSON data to an array
if($isValid){

    $existingArray = json_decode($existingData, true);
    
    // Append the new JSON object to the existing array
    $existingArray[] = $parsedData;
    
    // Encode the updated array back to JSON
    $newData = json_encode($existingArray, JSON_PRETTY_PRINT);
    // Save the updated JSON data back to the file
    file_put_contents($filename, $newData);
}

//************ */

// Token is valid, continue processing
$response = array(
    'mensaje' => 'Agregado Correctamente!',
    'status' => 200,
);

$jsonResponse = json_encode($response);

echo $jsonResponse;
?>
