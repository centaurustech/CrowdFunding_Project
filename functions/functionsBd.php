<?php

include '../../functions/conexaoBd.php';

/* ----------------------------------------------------------------------
 *                    FUNÇÕES DE INSERÇÃO
 * ---------------------------------------------------------------------- */

function cadastrarUsuario($tipo, $cpf, $nome, $email, $senha, $cep, $rua, $numero, $bairro, $cidade, $estado, $categoria, $dataNasc)
{
    $res = "INSERT INTO usuario (tipo, cpf, nome, email, senha, cep, rua, numero, bairro, cidade, estado, categoria, dataNasc)"
            . " VALUES ('$tipo', '$cpf', '$nome', '$email', '$senha', '$cep', '$rua', '$numero', '$bairro', '$cidade', '$estado',"
            . " '$categoria', '$dataNasc')";

    if (mysql_query($res))
    {
        echo "<script> alert('Usuário cadastrado com sucesso!'); "
        . "window.location='../projetoAprovado/projetosAprovados.php';</script>";
    } else
    {
        echo "<script> alert('Erro no cadastro do usuario!');"
        . " window.location='cadastrarUsuario.php';</script>";
    }
}

function cadastrarEditalOrcamento($ano, $valTotal, $cotaAluno, $cotaProf, $cotaServ)
{

    if (($cotaAluno + $cotaProf + $cotaServ) != 1)
    {
        echo "<script> alert('Erro no cadastro das cotas do Edital de Orçamento!');"
        . " window.location='cadastrarEditalCota.php';</script>";
    } else
    {
        // Pega QTD de usuários
        $SQL = mysql_query("SELECT COUNT(*) FROM usuario WHERE tipo = 'aluno'");
        $qtdAluno = mysql_result($SQL, 0);
        $SQL = mysql_query("SELECT COUNT(*) FROM usuario WHERE tipo = 'gestorProjeto'");
        $qtdProfessor = mysql_fetch_array($SQL);
        $SQL = mysql_query("SELECT COUNT(*) FROM usuario WHERE tipo = 'tecnico'");
        $qtdServ = mysql_fetch_array($SQL);

        // Realiza contas
        $valTotalAluno = $valTotal * $cotaAluno;
        $valTotalProfessor = $valTotal * $cotaProf;
        $valTotalServ = $valTotal * $cotaServ;

        if ($qtdAluno[0] != 0)
            $valIndAluno = $valTotalAluno / $qtdAluno[0];
        else
            $valIndAluno = 0;

        if ($qtdProfessor[0] != 0)
            $valIndProfessor = $valTotalProfessor / $qtdProfessor[0];
        else
            $valIndAluno = 0;

        if ($qtdServ[0] != 0)
            $valIndServ = $valTotalServ / $qtdServ[0];
        else
            $valIndAluno = 0;

        // ATUALIZAR FUNÇÃO
        $res = "INSERT INTO editalorcamento (ano, valTotal, cotaAluno, cotaProfessor, cotaServ, "
                . "qtdAluno, qtdProfessor, qtdServ, "
                . "valTotalAluno, valTotalProfessor, valTotalServ,"
                . "valIndAluno, valIndProfessor, valIndServ) VALUES "
                . "('$ano', '$valTotal', '$cotaAluno', '$cotaProf', '$cotaServ', "
                . "'[$qtdAluno[0]', '$qtdProfessor[0]', '$qtdServ[0]', "
                . "'$valTotalAluno', '$valTotalProfessor', '$valTotalServ',"
                . "'$valIndAluno', '$valIndProfessor', '$valIndServ')";

        if (mysql_query($res))
        {
            echo "<script> alert('Edital de Orçamento cadastrado com sucesso!'); "
            . "window.location='projetosAprovados.php';</script>";
        } else
        {
            echo "<script> alert('Erro no cadastro do Edital de Orçamento!');"
            . " window.location='cadastrarEditalCota.php';</script>";
        }
    }
}

function cadastrarProjetoCandidato($tipoFinanciamento, $categoria, $titulo, $location, $descricao, $duracao, $interValores, $dataInicio, $status, $valTotal, $autor, $resumo, $tmp_name)
{
    $res = null;

    // ATUALIZAR FUNÇÕES
    // Há Imagem, e há interVal!
    if ($location != null && $tipoFinanciamento == "modular")
    {
        $res = "INSERT INTO projeto (tipo, categoria, titulo, imagem, descricao, duracao, interValores, dataInicio, status, valorTotal, autor, resumo)"
                . " VALUES ('$tipoFinanciamento', '$categoria', '$titulo', '$location', '$descricao',"
                . "'$duracao', '$interValores', '$dataInicio', '$status', '$valTotal', '$autor', '$resumo')";
    }
    // Há Imagem, e NÃO há interVal!
    else if ($location != null && $tipoFinanciamento == "integral")
    {
        $res = "INSERT INTO projeto (tipo, categoria, titulo, imagem, descricao, duracao, dataInicio, status, valorTotal, autor, resumo)"
                . " VALUES ('$tipoFinanciamento', '$categoria', '$titulo', '$location', '$descricao',"
                . "'$duracao', '$dataInicio', '$status', '$valTotal', '$autor', '$resumo')";
    }

    if (mysql_query($res) and move_uploaded_file($tmp_name, $location))
    {
        echo "<script> alert('Projeto Candidato cadastrado com sucesso!'); "
        . "window.location='../../pages/projetoAprovado/projetosAprovados.php';</script>";
    } else
    {
        echo "<script> alert('Erro no cadastro da Cota de Financiamento!');"
        . " window.location='../../pages/projetoCandidato/cadastrarProjetoCandidato.php';</script>";
    }
}

function cadastraDoacao($idProjeto, $idAutor, $valor, $data)
{

    $queryAutor = procuraAutor($idAutor);
    $dadoAutor = mysql_fetch_array($queryAutor);
    $saldoAutor = $dadoAutor['saldo'] - $valor;

    $queryProjeto = consultaProjetoPorId($idProjeto);
    $dadoProjeto = mysql_fetch_array($queryProjeto);
    $saldoProjeto = $dadoProjeto['valArrecadado'] + $valor;

    $regDoacao = "INSERT INTO doacoes (idProjeto, idUsr, valor, data)"
            . " VALUES ('$idProjeto', '$idAutor', '$valor', '$data')";

    $atualizaSaldoProjeto = "UPDATE usuario SET saldo = '" . $saldoAutor . "' WHERE cpf = '" . $idAutor . "'";
    $atualizaSaldoUsr = "UPDATE projeto SET valArrecadado = '" . $saldoProjeto . "' WHERE id = '" . $idProjeto . "'";

    if (mysql_query($regDoacao) && (mysql_query($atualizaSaldoProjeto)) && (mysql_query($atualizaSaldoUsr)))
    {
        echo "<script> alert('Doação realizada com sucesso!'); "
        . "window.location='../../pages/projetoAprovado/projetosAprovados.php';</script>";
    } else
    {
        echo "<script> alert('Erro na doação!'); "
        . "window.location='../../pages/projetoAprovado/projetosAprovados.php';</script>";
    }
}

/* ----------------------------------------------------------------------
 *                    FUNÇÕES DE ATUALIZAÇÃO
 * ---------------------------------------------------------------------- */

function alterarUsuario($cpf, $nome, $email, $senha, $cep, $rua, $bairro, $cidade, $estado)
{
    $res = null;

    if ($nome != null && $email != null && $senha != null && $cep != null && $rua != null && $bairro != null && $cidade != null && $estado != null)
    // ATUALIZAR FUNÇÃO
        $res = "UPDATE usuario SET nome = '" . $nome . "', email = '" . $email . "', senha = '" . $senha . "', cep = '" . $cep . "', "
                . "rua = '" . $rua . "', bairro = '" . $bairro . "', cidade = '" . $cidade . "', estado = '" . $estado . "' "
                . "WHERE cpf = '" . $cpf . "'";

    if (mysql_query($res))
    {
        echo "<script> alert('Usuario atualizado com sucesso!'); "
        . "window.location='../../pages/projetoAprovado/projetosAprovados.php';</script>";
    } else
    {
        echo "<script> alert('Erro na atualização!'); "
        . "window.location='../../pages/usuario/alterarUsuario.php';</script>";
    }
}

function desativaUsuario($cpf)
{
    $res = "UPDATE usuario SET status = 'desativo'"
                . "WHERE cpf = '" . $cpf . "'";

    if (mysql_query($res))
    {
        echo "<script> alert('Usuario desativado com sucesso!'); "
        . "window.location='../../pages/projetoAprovado/projetosAprovados.php';</script>";
    } else
    {
        echo "<script> alert('Erro na desativação!'); "
        . "window.location='../../pages/usuario/alterarUsuario.php';</script>";
    }
}

function ativaUsuario($cpf)
{
    $res = "UPDATE usuario SET status = 'ativo'"
                . "WHERE cpf = '" . $cpf . "'";

    if (mysql_query($res))
    {
        echo "<script> alert('Usuario ativado com sucesso!'); "
        . "window.location='../../pages/usuario/alterarUsuario.php';</script>";
    } else
    {
        echo "<script> alert('Erro na desativação!'); "
        . "window.location='../../pages/usuario/alterarUsuario.php';</script>";
    }
}



/* ----------------------------------------------------------------------
 *                    FUNÇÕES DE CONSULTA
 * ---------------------------------------------------------------------- */

function consultaEditalOrcamento()
{
    RETURN mysql_query("SELECT ano, valTotal FROM editalorcamento");
}

function consultaQtdApoiadores($id)
{
    RETURN mysql_query("SELECT COUNT(*) FROM doacoes WHERE idProjeto = '" . $id . "'");
}

function consultaCotaFinanciameno()
{
    RETURN mysql_query("SELECT * FROM editalorcamento");
}

function consultaCriterios()
{
    RETURN mysql_query("SELECT * FROM criterios");
}

function consultaProjeto($status)
{
    RETURN mysql_query("SELECT * FROM projeto WHERE status = '" . $status . "'");
}

function consultaProjetoPorId($id)
{
    RETURN mysql_query("SELECT * FROM projeto WHERE id = '" . $id . "'");
}

function consultaProjetoPorAutor($status, $autor)
{
    RETURN mysql_query(("SELECT * FROM projeto WHERE status = '" . $status . "' AND autor = '" . $autor . "'"));
}

function procuraAutor($autor)
{
    RETURN mysql_query("SELECT * FROM usuario WHERE cpf = '" . $autor . "'");
}

function consultaDoacaoPorIdProjeto($id)
{
    RETURN mysql_query("SELECT * FROM doacoes WHERE idProjeto = '" . $id . "'");
}

function traduzTipoAutor($tipo)
{
    if ($tipo == 'gestorProjeto')
        RETURN "Gestor de Projetos";
    else if ($tipo == 'aluno')
        RETURN "Aluno";
    else if ($tipo == 'tecnico')
        RETURN "Técnico Administrativo";
}

function consultaUsuario()
{
    RETURN mysql_query("SELECT * FROM usuario ORDER BY nome");
}

function procuraProjetoAtivodeAtor($cpf)
{
    $query = mysql_query("SELECT * FROM projeto WHERE autor = '".$cpf."' AND status <> 'concluido'");
    if(mysql_fetch_array($query))
        return 0;
    else
        return 1;
}

/* ----------------------------------------------------------------------
 *                            AVALIAR PROJETO
 * ---------------------------------------------------------------------- */

function avaliarProjetoCandidato($id, $aval, $desc, $crit1, $crit2, $crit3)
{
    if ($aval == 'aprovado')
    {
        $query = consultaProjetoPorId($id);
        $dados = mysql_fetch_array($query);
        date_default_timezone_set('America/Sao_Paulo');
        $dataInicio = date('Y-m-d');
        $duracao = $dados['duracao'];
        $dataFim = new DateTime($dataInicio);
        $dataFim->add(new DateInterval('P' . $duracao . 'D'));
        $dataFinal = $dataFim->format('Y-m-d');
    }
    if (mysql_query($res))
    {
        echo "<script> alert('Projeto Avaliado Com Sucesso!'); "
        . "window.location='../../pages/projetoAprovado/projetosAprovados.php';</script>";
    } else
    {
        echo "<script> alert('Erro na avaliaçao!'); "
        . "window.location='../../pages/projetoCandidato/avaliarProjetoCandidato.php';</script>";
    }
}

/* ----------------------------------------------------------------------
 *                                 EXCLUSÕES
 * ---------------------------------------------------------------------- */



?>