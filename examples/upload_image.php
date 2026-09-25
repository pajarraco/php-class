<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use PhpClass\Upload\ImgUploader;
use RuntimeException;

$message = 'Cargar Imagen';
$uploadedPath = null;

if (isset($_POST['form'])) {
    try {
        $imgUploader = new ImgUploader();
        $message = $imgUploader->upload($_FILES['img_file']) ?? $message;

        if ($imgUploader->filename !== '') {
            $uploadedPath = $imgUploader->pathImages . '/' . $imgUploader->filename;
        }
    } catch (RuntimeException $exception) {
        $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Subir Imagen</title>
</head>

<body>
<div align="center" class="Titulos"><?= $message ?><br />
<?php if ($uploadedPath !== null): ?>
<?= htmlspecialchars($uploadedPath, ENT_QUOTES) ?>
<?php endif; ?>
</div>
<form name="form" method="POST" enctype="multipart/form-data">
  <table width="100%" border="0" align="center">
    <tr>
      <td width="50%" align="right">Selecciona una imagen:
      </td>
      <td><input name="img_file" type="file" size="35" id="img_file" /></td>
    </tr>
    <tr>
      <td width="50%" align="right">
      <input name="MM_insert" type="hidden" id="MM_insert" value="form" /></td>
      <td><input type="submit" name="form" value="Subir Archivo" /></td>
    </tr>
  </table>
</form>
</body>
</html>
