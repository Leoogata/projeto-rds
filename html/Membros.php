<?php include "../inc/dbinfo.inc"; ?>
<html>
  <body>
    <h1>Membros</h1>
<?php
  // Conexão
  $constring = "host=" . DB_SERVER . " dbname=" . DB_DATABASE . " user=" . DB_USERNAME . " password=" . DB_PASSWORD;
  $connection = pg_connect($constring);
  if (!$connection) { echo "Falha na conexão com o banco de dados"; exit; }

  // Garante a existência da tabela
  VerifyMembrosTable($connection);

  // Se recebeu POST, insere novo membro
  $nome     = trim($_POST['NOME'] ?? "");
  $nota     = trim($_POST['NOTA'] ?? "");
  $presenca = trim($_POST['PRESENCA'] ?? "");

  if ($nome !== "" && $nota !== "" && $presenca !== "") {
    AddMembro($connection, $nome, $nota, $presenca);
  }
?>
    <!-- Formulário -->
    <form action="<?php echo $_SERVER['SCRIPT_NAME'] ?>" method="POST">
      <table border="0">
        <tr>
          <td>Nome</td><td>Nota</td><td>Presença</td>
        </tr>
        <tr>
          <td><input type="text" name="NOME" maxlength="100" size="30" /></td>
          <td><input type="number" step="0.01" name="NOTA" /></td>
          <td><input type="number" name="PRESENCA" /></td>
          <td><input type="submit" value="Adicionar" /></td>
        </tr>
      </table>
    </form>

    <!-- Lista -->
    <h2>Lista de Membros</h2>
    <table border="1" cellpadding="2" cellspacing="2">
      <tr>
        <td>ID</td><td>Nome</td><td>Nota</td><td>Presença</td>
      </tr>
<?php
  $result = pg_query($connection, "SELECT ID, NOME, NOTA, PRESENCA FROM MEMBROS ORDER BY ID DESC");
  while ($row = pg_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>".htmlentities($row['nome'])."</td>";
    echo "<td>{$row['nota']}</td>";
    echo "<td>{$row['presenca']}</td>";
    echo "</tr>";
  }

  pg_free_result($result);
  pg_close($connection);

  // Funções
  function AddMembro($connection, $nome, $nota, $presenca) {
    $n = pg_escape_string($nome);
    $nota = (float)$nota;
    $presenca = (int)$presenca;
    $query = "INSERT INTO MEMBROS (NOME, NOTA, PRESENCA) VALUES ('$n', $nota, $presenca)";
    if (!pg_query($connection, $query)) echo "<p>Erro ao inserir membro.</p>";
  }

  function VerifyMembrosTable($connection) {
    $exists = pg_query($connection, "SELECT 1 FROM information_schema.tables WHERE table_name='membros'");
    if (pg_num_rows($exists) == 0) {
      $query = "CREATE TABLE MEMBROS (
        ID SERIAL PRIMARY KEY,
        NOME VARCHAR(100) NOT NULL,
        NOTA NUMERIC(4,2) NOT NULL,
        PRESENCA INTEGER NOT NULL DEFAULT 0
      )";
      if (!pg_query($connection, $query)) echo "<p>Erro ao criar tabela.</p>";
    }
  }
?>
  </body>
</html>
