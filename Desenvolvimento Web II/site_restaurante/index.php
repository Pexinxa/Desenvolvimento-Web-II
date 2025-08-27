<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Pizzaria</title>
</head>

<body>
    <section>
        <h1>Pizzaria</h1>
        <h3>Novo Pedido</h3>
        <form action="#" method="post">
            <label for="txtnome">Seu nome:</label>
            <input type="text" name="txtnome">
            <br><br>
            <label for="sabor">Escolha o sabor da pizza:</label>
            <select name="sltsabor" id="sabores">
                <option value="Mussarela">Mussarela</option>
                <option value="Calabresa">Calabresa</option>
                <option value="Caipira">Caipira</option>
                <option value="Brocolis">Brócolis</option>
            </select>
            <br><br>
            <label for="">Borda recheada:</label>
            <input type="radio" value="Não" name="rdborda">Não
            <input type="radio" value="Sim" name="rdborda">Sim (+ R$5,00)
            <br><br>
            <label for="">Bebidas (você pode escolher mais de uma):</label><br>
            <input type="checkbox" value="Refri" name="bebidas[]">Refri de lata - R$8,00 <br>
            <input type="checkbox" value="Suco" name="bebidas[]">Suco 500ml - R$12,00 <br>
            <input type="checkbox" value="Água" name="bebidas[]">Água 500ml - R$5,00 <br>
            <br><br>
            <input type="submit" value="Finalizar Pedido">
        </form>
    </section>

</html>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST["txtnome"]) ? $_POST["txtnome"] : '';
    $sabor = isset($_POST["sltsabor"]) ? $_POST["sltsabor"] : '';
    $borda = isset($_POST["rdborda"]) ? $_POST["rdborda"] : 'Não';
    $bebidas = isset($_POST["bebidas"]) ? $_POST["bebidas"] : [];

    $precoBase = 0;

    if ($sabor == "Mussarela") {
        $precoBase = 30;
    } else if ($sabor == "Calabresa") {
        $precoBase = 32;
    } else if ($sabor == "Caipira") {
        $precoBase = 35;
    } else if ($sabor != '') {
        $precoBase = 37;
    }

    if ($borda == "Sim") {
        $precoBase += 5;
    }

    $precosBebidas = [
        "Refri" => 8,
        "Suco" => 12,
        "Água" => 5
    ];

    $totalBebidas = 0;
    foreach ($bebidas as $bebida) {
        if (isset($precosBebidas[$bebida])) {
            $totalBebidas += $precosBebidas[$bebida];
        }
    }

    $total = $precoBase + $totalBebidas;

    if (!empty($nome) && !empty($sabor)) {
        echo "<div class='resumo'>";
        echo "<u>Resumo do Pedido</u>";
        echo "<br><strong>Nome:</strong> " . htmlspecialchars($nome);
        echo "<br><strong>Sabor da pizza:</strong> " . htmlspecialchars($sabor) . " (R$ " . $precoBase . ")";
        echo "<br><strong>Borda recheada:</strong> " . htmlspecialchars($borda);

        if (!empty($bebidas)) {
            echo "<br><strong>Bebidas:</strong> ";
            echo implode(", ", array_map('htmlspecialchars', $bebidas));
        } else {
            echo "<br><strong>Bebidas:</strong> Nenhuma";
        }

        echo "<br><strong>Total a pagar:</strong> R$ " . number_format($total, 2, ',', '.');
        echo "</div>";
    }
}
?>