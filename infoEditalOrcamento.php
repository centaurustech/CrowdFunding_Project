<?php
require_once './validateSessionFunctions.php';
require_once './functionsBd.php';
validateHeader();
?>

<section id="infoEditalOrcamento" class="container">
    <div class="container">

        <h2 class="sub-header">Editais de Orçamento</h2>
        <div class="table-responsive">

            <!-- CRIAÇÃO DA TABELA DINÂMICA -->
            <script>$(document).ready(function ()
                {
                    $('#listarEditalOrcamento').DataTable();
                });
            </script>
            <!-- FIM DA CRIAÇÃO -->

            <table id="listarEditalOrcamento" class="table table-striped">
                <thead>
                    <tr>
                        <th>Ano</th>
                        <th>Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = consultaEditalOrcamento();
                    while ($dados = mysql_fetch_array(($query)))
                    {
                        echo "<tr>"
                        . "<td> " . $dados['ano'] . "</td>
                                       <td> R$ " . number_format($dados['valTotal'], 2, ',', '.') . "</td>"
                        . "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <hr/>
    <?php
    if (isset($_SESSION["tipoUsr"]) && (strcmp($_SESSION["tipoUsr"], "gestorOrcamentario") == 0))
    {
        echo " 
        <br/><br/>
        <div style='text-align: center; border-top-width: 100px'>
            <div style='margin-left:30px;'>
                <!-- Caso exista, o sistema de recompensas será demonstrado aqui -->
                <a href='cadastrarEditalCota.php'><input type='button' class='btn-primary' value='Cadastrar Novo Edital de Orçamento'></a>
            </div>
        </div>
    ";
    }
    ?>

</section>
<?php include("footer.php") ?>