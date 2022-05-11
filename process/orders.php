<?php

include_once("conn.php");

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {

    $pedidosQuery = $conn->query("SELECT * FROM pedido;");

    $pedidos = $pedidosQuery->fetchAll();

    $pizzas = [];

    // Montando pizza
    foreach ($pedidos as $pedido) {

        $pizza = [];

        // definir um array para a pizza
        $pizza["id"] = $pedido["pizzas_id"];

        // resgatando a pizza
        $pizzaQuery = $conn->prepare("SELECT * FROM pizza WHERE id = :pizza_id");

        $pizzaQuery->bindParam(":pizza_id", $pizza["id"]);

        $pizzaQuery->execute();

        $pizzaData = $pizzaQuery->fetch(PDO::FETCH_ASSOC);

        // resgatando a borda da pizza
        $bordaQuery = $conn->prepare("SELECT * FROM edges WHERE id = :edge_id");

        $bordaQuery->bindParam(":edge_id", $pizzaData["edge_id"]);

        $bordaQuery->execute();

        $borda = $bordaQuery->fetch(PDO::FETCH_ASSOC);

        $pizza["borda"] = $borda["tipo"];

        // resgatando a massa da pizza
        $massaQuery = $conn->prepare("SELECT * FROM pastas WHERE id = :pasta_id");

        $massaQuery->bindParam(":pasta_id", $pizzaData["pasta_id"]);

        $massaQuery->execute();

        $massa = $massaQuery->fetch(PDO::FETCH_ASSOC);

        $pizza["massa"] = $massa["tipo"];

        // resgatando os sabores da pizza
        $saboresQuery = $conn->prepare("SELECT * FROM pizza_flavor WHERE pizzas_id = :pizzas_id");

        $saboresQuery->bindParam(":pizzas_id", $pizza["id"]);

        $saboresQuery->execute();

        $sabores = $saboresQuery->fetchAll(PDO::FETCH_ASSOC);

        // resgatando o nome dos sabores
        $saboresDaPizza = [];

        $saborQuery = $conn->prepare("SELECT * FROM flavors WHERE id = :flavor_id");

        foreach($sabores as $sabor) {

            $saborQuery->bindParam(":flavor_id", $sabor["flavor_id"]);

            $saborQuery->execute();

            $saborPizza = $saborQuery->fetch(PDO::FETCH_ASSOC);

            array_push($saboresDaPizza, $saborPizza["nome"]);

        }

        $pizza["sabores"] = $saboresDaPizza;

        // adicionar o status do pedido
        $pizza["status"] = $pedido["status_id"];
        
        // Adicionar o array de pizza, ao array das pizzas
        array_push($pizzas, $pizza);

    }

    // Resgatando os status
    $statusQuery = $conn->query("SELECT * FROM statu;");

    $status = $statusQuery->fetchAll();

} else if ($method === "POST") {

    // verificando tipo de POST
    $type = $_POST["type"];

    // deletar pedido
    if($type === "delete") {

        $pizzaId = $_POST["id"];

        $deleteQuery = $conn->prepare("DELETE FROM pedido WHERE pizzas_id = :pizzas_id;");

        $deleteQuery->bindParam(":pizzas_id", $pizzaId, PDO::PARAM_INT);

        $deleteQuery->execute();

        $_SESSION["msg"] = "Pedido removido com sucesso!";
        $_SESSION["status"] = "success";

    //Atualizar status do pedido    
    } else if($type === "update") {

        $pizzaId = $_POST["id"];
        $statusId = $_POST["status"];

        $updateQuery = $conn->prepare("UPDATE pedido SET status_id = :status_id WHERE pizzas_id = :pizzas_id");

        $updateQuery->bindParam(":pizzas_id", $pizzaId, PDO::PARAM_INT);
        $updateQuery->bindParam(":status_id", $statusId, PDO::PARAM_INT);

        $updateQuery->execute();
        
        $_SESSION["msg"] = "pedido atualizado com sucesso!";
        $_SESSION["status"] = "success";

    }

    // retorna usuário para dashboard
    header("Location: ../dashboard.php");

}

?>