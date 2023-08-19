<?php
header('Content-Type: application/json');
$fileContent = file_get_contents('./Data/DB.txt');
$jsonData = json_decode($fileContent, true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(array('mensaje' => 'Metodo no permitido'));
    exit();
}
$authHeader = $_SERVER['HTTP_AUTHORIZATION'];

$token = str_replace('Bearer ', '', $authHeader);

$validToken = 'UgovWflFf3LBUx';

if ($token !== $validToken) {
    // Unauthorized, invalid token
    http_response_code(401);
    echo json_encode(array('mensaje' => 'NO Autorizado'));
    exit();
}


function filterBy($data, $startDate="", $endDate="",$phoneNumber="") {
    $filteredData = [];
    
    foreach ($data as $item) {
        $itemDate = $item['fecha'];
        $itemPhone = $item['telefono'];
        if (($itemDate >= $startDate && $itemDate <= $endDate)||$phoneNumber===$itemPhone) {
            $filteredData[] = $item;
        }
    }
    return $filteredData;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['fecha_inicio']??null;
    $end_date = $_POST['fecha_fin']??null;
    $phone =$_POST['telefono']??null;
    if(($start_date===null || $start_date==='') && ($end_date===null || $end_date==='') && ($phone===null || $phone==='') ){
        echo json_encode(['Numero de resultados'=>count($jsonData),'Resultados'=>$jsonData]);
    }else{
        $results = filterBy($jsonData,$start_date,$end_date,$phone);
        if(count($results)===0){
            echo json_encode(["mensaje"=>"No se encontraron coincidencias con base a los filtros."]);
        }
         else{
             echo json_encode(['Numero de resultados'=>count($results) ,'Resultados'=>$results]);
         } 
    }
}

?>
