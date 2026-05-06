<?php
// Ativa exibicao de erros apenas durante testes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define o caminho das fontes do FPDF
define('FPDF_FONTPATH', '/home/viacaope/public_html/font/');
require('fpdf.php');

$destino = "email.hotmail@gmail.com";

// ========================
// FUNÇÃO MELHORADA PARA ACENTOS (mais robusta)
function removerAcentos($texto) {
    if (empty($texto)) return '';

    // Tenta iconv primeiro (melhor para transliteração)
    $texto = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);

    // Fallback com mb_convert_encoding
    if ($texto === false || empty($texto)) {
        $texto = @mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }

    // Mapeamento manual final para garantir Ç, ç, ã, õ, etc.
    $mapa = [
        'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
        'Á'=>'A','À'=>'A','Ã'=>'A','Â'=>'A','Ä'=>'A',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
        'É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
        'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I',
        'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
        'Ó'=>'O','Ò'=>'O','Õ'=>'O','Ô'=>'O','Ö'=>'O',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
        'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U',
        'ç'=>'c','Ç'=>'C',
        'ñ'=>'n','Ñ'=>'N'
    ];

    return strtr($texto, $mapa);
}

// ========================
// CAPTURA DOS DADOS
// ========================
$nome                = trim($_POST['nome'] ?? '');
$email               = trim($_POST['email'] ?? '');
$telefone            = trim($_POST['telefone'] ?? '');
$data_nascimento     = trim($_POST['data_nascimento'] ?? '');
$cep                 = trim($_POST['cep'] ?? '');
$endereco            = trim($_POST['endereco'] ?? '');
$numero              = trim($_POST['numero'] ?? '');
$bairro              = trim($_POST['bairro'] ?? '');
$cidade              = trim($_POST['cidade'] ?? '');
$cargo               = trim($_POST['cargo'] ?? '');
$escolaridade_cursos = trim($_POST['escolaridade_cursos'] ?? '');
$categoria_cnh       = trim($_POST['categoria_cnh'] ?? '');
$numero_cnh          = trim($_POST['numero_cnh'] ?? '');

// Experiências Profissionais (array)
$empresas      = $_POST['empresa'] ?? [];
$cargos_exp    = $_POST['cargo_exp'] ?? [];
$data_admissao = $_POST['data_admissao'] ?? [];
$data_demissao = $_POST['data_demissao'] ?? [];
$atividades    = $_POST['atividades'] ?? [];

$experiencias = [];
for ($i = 0; $i < count($empresas); $i++) {
    if (!empty(trim($empresas[$i] ?? ''))) {
        $experiencias[] = [
            'empresa'    => trim($empresas[$i]),
            'cargo'      => trim($cargos_exp[$i] ?? ''),
            'admissao'   => trim($data_admissao[$i] ?? ''),
            'demissao'   => trim($data_demissao[$i] ?? ''),
            'atividades' => trim($atividades[$i] ?? '')
        ];
    }
}

// Validação
if (empty($nome) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Nome e e-mail são obrigatórios.");
}

// Arquivo do currículo
$nomeArquivo = $_FILES['curriculo']['name'] ?? 'sem_curriculo';
$tmpArquivo  = $_FILES['curriculo']['tmp_name'] ?? '';

// ========================
// GERAÇÃO DO PDF
// ========================
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 20);

// Cabeçalho
$pdf->SetFont('Helvetica', 'B', 18);
$pdf->Cell(0, 12, 'VIACAO ELITE', 0, 1, 'C');
$pdf->SetFont('Helvetica', 'B', 14);
$pdf->Cell(0, 10, 'FORMULARIO DE CANDIDATURA', 0, 1, 'C');
$pdf->Ln(8);

$pdf->SetFont('Helvetica', 'I', 11);
$pdf->Cell(0, 8, 'Data de envio: ' . date("d/m/Y"), 0, 1, 'R');
$pdf->Ln(5);

// Dados do Candidato
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(0, 8, 'DADOS DO CANDIDATO', 'B', 1);
$pdf->Ln(3);
$pdf->SetFont('Helvetica', '', 11);

$pdf->Cell(50, 7, 'Nome:', 0, 0);
$pdf->MultiCell(0, 7, removerAcentos($nome), 0, 'L');

$pdf->Cell(50, 7, 'Data de nascimento:', 0, 0);
$pdf->MultiCell(0, 7, removerAcentos($data_nascimento ?: 'Nao informado'), 0, 'L');

$pdf->Cell(50, 7, 'E-mail:', 0, 0);
$pdf->MultiCell(0, 7, $email, 0, 'L');

$pdf->Cell(50, 7, 'Telefone:', 0, 0);
$pdf->MultiCell(0, 7, $telefone, 0, 'L');

$pdf->Cell(50, 7, 'CEP:', 0, 0);
$pdf->MultiCell(0, 7, $cep ?: 'Nao informado', 0, 'L');

$pdf->Cell(50, 7, 'Endereco:', 0, 0);
$enderecoCompleto = $endereco ?: 'Nao informado';
if (!empty($numero)) $enderecoCompleto .= ", $numero";
$pdf->MultiCell(0, 7, removerAcentos($enderecoCompleto), 0, 'L');

$pdf->Cell(50, 7, 'Bairro:', 0, 0);
$pdf->MultiCell(0, 7, removerAcentos($bairro ?: 'Nao informado'), 0, 'L');

$pdf->Cell(50, 7, 'Cidade:', 0, 0);
$pdf->MultiCell(0, 7, removerAcentos($cidade ?: 'Nao informado'), 0, 'L');

$pdf->Cell(50, 7, 'Cargo desejado:', 0, 0);
$pdf->MultiCell(0, 7, removerAcentos($cargo), 0, 'L');

if (!empty($categoria_cnh)) {
    $pdf->Cell(50, 7, 'CNH:', 0, 0);
    $cnh_texto = removerAcentos($categoria_cnh);
    if (!empty($numero_cnh)) $cnh_texto .= " (n " . $numero_cnh . ")";
    $pdf->MultiCell(0, 7, $cnh_texto, 0, 'L');
}

$pdf->Ln(8);

// Escolaridade
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(0, 8, 'ESCOLARIDADE E CURSOS', 'B', 1);
$pdf->Ln(3);
$pdf->SetFont('Helvetica', '', 11);
$pdf->MultiCell(0, 7, removerAcentos($escolaridade_cursos ?: 'Nao informado'), 0, 'L');
$pdf->Ln(8);

// Experiência Profissional
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(0, 8, 'EXPERIENCIA PROFISSIONAL', 'B', 1);
$pdf->Ln(5);
$pdf->SetFont('Helvetica', '', 11);

if (empty($experiencias)) {
    $pdf->MultiCell(0, 7, 'Nao informado', 0, 'L');
} else {
    foreach ($experiencias as $exp) {
        $periodo = !empty($exp['admissao']) ? $exp['admissao'] : '';
        if (!empty($exp['demissao'])) {
            $periodo .= " - " . $exp['demissao'];
        } elseif (!empty($exp['admissao'])) {
            $periodo .= " - Atual";
        }

        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->MultiCell(0, 7, removerAcentos($exp['empresa']), 0, 'L');
        
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->Cell(0, 7, removerAcentos($exp['cargo']) . "   |   " . $periodo, 0, 1);
        
        if (!empty($exp['atividades'])) {
            $pdf->MultiCell(0, 7, "Atividades: " . removerAcentos($exp['atividades']), 0, 'L');
        }
        $pdf->Ln(6);
    }
}

// Gera o PDF em memória
$pdfContent = $pdf->Output('S');

// ========================
// ENVIO DO E-MAIL
// ========================
$boundary = md5(time());
$headers = "MIME-Version: 1.0\r\n";
$headers .= "From: noreply@viacaoelite.com.br\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$corpo = "--$boundary\r\n";
$corpo .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$corpo .= "Novo curriculo recebido via site\n\n";
$corpo .= "Nome: $nome\n";
$corpo .= "Cargo desejado: $cargo\n\n";
$corpo .= "Endereco: " . ($endereco ?: "Nao informado") . (!empty($numero) ? ", $numero" : "") . "\n";
$corpo .= "Bairro: " . ($bairro ?: "Nao informado") . "\n";
$corpo .= "Cidade: " . ($cidade ?: "Nao informado") . "\n\n";
$corpo .= "CNH: " . ($categoria_cnh ?: "Nao informado") . (!empty($numero_cnh) ? " (nº " . $numero_cnh . ")" : "") . "\n";
$corpo .= "Escolaridade: " . ($escolaridade_cursos ?: "Nao informado") . "\n\n";

$corpo .= "=== EXPERIÊNCIA PROFISSIONAL ===\n\n";
if (empty($experiencias)) {
    $corpo .= "Nao informado\n";
} else {
    foreach ($experiencias as $exp) {
        $periodo = !empty($exp['admissao']) ? $exp['admissao'] : '';
        if (!empty($exp['demissao'])) $periodo .= " - " . $exp['demissao'];
        else $periodo .= " - Atual";

        $corpo .= "Empresa: " . $exp['empresa'] . "\n";
        $corpo .= "Cargo: " . $exp['cargo'] . "\n";
        $corpo .= "Período: " . $periodo . "\n";
        if (!empty($exp['atividades'])) {
            $corpo .= "Atividades: " . $exp['atividades'] . "\n";
        }
        $corpo .= "-------------------------------\n\n";
    }
}

// Anexo do PDF gerado
$conteudoPDF = chunk_split(base64_encode($pdfContent));
$corpo .= "--$boundary\r\n";
$corpo .= "Content-Type: application/pdf; name=\"Comprovante_Candidatura_$nome.pdf\"\r\n";
$corpo .= "Content-Transfer-Encoding: base64\r\n";
$corpo .= "Content-Disposition: attachment; filename=\"Comprovante_Candidatura_$nome.pdf\"\r\n\r\n";
$corpo .= $conteudoPDF . "\r\n";

// Anexo do currículo do candidato
if (!empty($tmpArquivo) && is_uploaded_file($tmpArquivo)) {
    $conteudo = chunk_split(base64_encode(file_get_contents($tmpArquivo)));
    $corpo .= "--$boundary\r\n";
    $corpo .= "Content-Type: application/octet-stream; name=\"$nomeArquivo\"\r\n";
    $corpo .= "Content-Transfer-Encoding: base64\r\n";
    $corpo .= "Content-Disposition: attachment; filename=\"$nomeArquivo\"\r\n\r\n";
    $corpo .= $conteudo . "\r\n";
}

$corpo .= "--$boundary--";

$enviado = mail($destino, "Novo Curriculo - $nome ($cargo)", $corpo, $headers);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $enviado ? 'Curriculo Enviado com Sucesso' : 'Erro ao Enviar'; ?> | Viacao Elite</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .mensagem-container {
            max-width: 700px;
            margin: 60px auto;
            padding: 50px 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 25px rgba(0,0,0,0.12);
            text-align: center;
        }
        .icone-sucesso { font-size: 80px; color: #28a745; margin-bottom: 20px; }
        .icone-erro { font-size: 80px; color: #dc3545; margin-bottom: 20px; }
        .titulo { color: #003f7d; margin-bottom: 25px; }
        .texto { font-size: 1.15em; line-height: 1.6; color: #444; margin-bottom: 35px; }
        .btn-voltar {
            display: inline-block;
            background: #003f7d;
            color: white;
            padding: 16px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.15em;
            transition: background 0.3s ease;
        }
        .btn-voltar:hover { background: #002b5c; }
    </style>
</head>
<body>
<header>
    <div class="container nav">
        <nav class="menu" aria-label="Menu principal">
            <a href="index.html">Inicio</a>
            <a href="index.html#servicos">Servicos</a>
            <a href="index.html#contato">Contato</a>
        </nav>
    </div>
</header>
<main>
    <section class="mensagem-container">
        <?php if ($enviado): ?>
            <div class="icone-sucesso">✔</div>
            <h1 class="titulo">Curriculo Enviado com Sucesso!</h1>
            <p class="texto">
                Obrigado, <strong><?php echo htmlspecialchars($nome); ?></strong>!<br>
                Seu curriculo foi recebido com sucesso pela Viacao Elite.<br>
                Nossa equipe analisara seu perfil e retornaremos em breve.
            </p>
            <p style="color: #666; margin-bottom: 40px;">
                Uma copia do comprovante foi enviada para o e-mail informado.<br>
                (verifique tambem a pasta de spam/promocoes)
            </p>
        <?php else: ?>
            <div class="icone-erro">✖</div>
            <h1 class="titulo">Erro ao Enviar Curriculo</h1>
            <p class="texto">
                Nao foi possivel enviar seu curriculo no momento.<br>
                Por favor, tente novamente ou envie diretamente para:<br>
                <strong>rh@viacaoElite.com.br</strong>
            </p>
        <?php endif; ?>
        <a href="index.html" class="btn-voltar">Voltar ao Inicio</a>
    </section>
</main>
<footer>
    <div class="container" style="text-align: center; padding: 25px 0; color: #777; font-size: 0.95em;">
        © <?php echo date("Y"); ?> Viacao Elite. Todos os direitos reservados.
    </div>
</footer>
</body>
</html>
