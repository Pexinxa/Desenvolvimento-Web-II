<?php
require_once 'tema.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <title>Quiz Anime</title>
</head>
<body>
    <h1 class="titulo">Quiz Anime</h1>

    <?php

    $etapa = $_POST['etapa'] ?? 'tema';

    if ($etapa === 'tema') : ?>
        <form method="POST" class="form">
            <label class="tema">Escolha o tema:</label>
            <select name="temas" class="select" required>
                <?php foreach ($quiz as $tema => $perguntas) : ?>
                    <option value="<?= htmlspecialchars($tema) ?>"><?= htmlspecialchars($tema) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="etapa" value="perguntas">
            <input type="submit" value="Iniciar Quiz" class="botao">
        </form>

    <?php

    elseif ($etapa === 'perguntas' && isset($_POST['temas'])) :
        $temaEscolhido = $_POST['temas']; ?>
        
        <form method="POST" class="form">
            <h2 class="tema">Tema escolhido: <?= htmlspecialchars($temaEscolhido) ?></h2>
            <div class="caixa-perguntas">
                <?php foreach ($quiz[$temaEscolhido] as $index => $pergunta) : ?>
                    <div class="pergunta">
                        <h3>Pergunta <?= $index + 1 ?>: <?= htmlspecialchars($pergunta['pergunta']) ?></h3>
                        <ul>
                            <?php foreach ($pergunta['opcoes'] as $i => $opcao) : ?>
                                <li>
                                    <input 
                                        type="radio" 
                                        name="pergunta_<?= $index ?>" 
                                        value="<?= htmlspecialchars($opcao) ?>" 
                                        <?= $i === 0 ? 'required' : '' ?>
                                    > <?= htmlspecialchars($opcao) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="hidden" name="temas" value="<?= htmlspecialchars($temaEscolhido) ?>">
            <input type="hidden" name="etapa" value="resultado">
            <input type="submit" value="Enviar Respostas" class="botao">
        </form>

    <?php

    elseif ($etapa === 'resultado' && isset($_POST['temas'])) :
        $temaEscolhido = $_POST['temas'];
        $pontuacao = 0;

        foreach ($quiz[$temaEscolhido] as $index => $pergunta) {
            $respostaCerta = $pergunta['resposta'] ?? null;
            $respostaDada = $_POST["pergunta_$index"] ?? null;

            if ($respostaCerta && $respostaDada === $respostaCerta) {
                $pontuacao++;
            }
        } ?>

        <form method="POST" class="form">
            <h2 class="tema">Resultado do tema: <?= htmlspecialchars($temaEscolhido) ?></h2>
            <h3>Sua pontuação: <?= $pontuacao ?> / <?= count($quiz[$temaEscolhido]) ?></h3>
            <input type="hidden" name="etapa" value="tema">
            <input type="submit" value="Fazer novamente" class="botao">
        </form>

    <?php endif; ?>
</body>
</html>
