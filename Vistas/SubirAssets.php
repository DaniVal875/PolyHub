<?php require_once '../Controladores/Php/SubirseAssets.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PolyHub - <?= $es_edicion ? 'Editar Asset' : 'Subir Nuevo Asset' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #09090a; color: #fff; margin: 0; padding: 0; }
        .navbar { display: flex; align-items: center; justify-content: space-between; padding: 15px 40px; background: #000; border-bottom: 1px solid #111; }
        .nav-left { display: flex; align-items: center; gap: 30px; }
        .btn-ver-assets { background: #222; color: #fff; border: none; padding: 10px 18px; font-weight: 600; border-radius: 6px; cursor: pointer; }
        .brand-explorar { font-weight: 800; text-transform: uppercase; font-size: 1.1rem; }
        .nav-center { display: flex; align-items: center; gap: 35px; margin-left: auto; margin-right: 40px; }
        .nav-link-main { font-weight: 700; text-transform: uppercase; font-size: 0.85rem; color: #fff; cursor: pointer; }
        .main-content { padding: 60px 20px; }
        .upload-container { max-width: 750px; margin: 0 auto; background: #111112; padding: 40px; border-radius: 12px; border: 1px solid #222; }
        .form-group { margin-bottom: 24px; display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-weight: 600; font-size: 0.9rem; color: #b3b3b3; }
        .form-control { padding: 12px; background: #1c1c1e; border: 1px solid #3a3a3c; border-radius: 8px; color: #fff; font-size: 0.95rem; outline: none; }
        .row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        /* Switch styling */
        .switch-container { display: flex; align-items: center; gap: 15px; margin-top: 10px; }
        .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #3a3a3c; transition: .3s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
        input:checked + .slider { background-color: #227daaa8; }
        input:checked + .slider:before { transform: translateX(24px); }
        
        /* Botones fundamentales */
        .btn-group { display: flex; gap: 15px; margin-top: 35px; justify-content: flex-end; }
        .btn { padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; }
        .btn-primary { background: #227daaa8; color: white; }
        .btn-secondary { background: #2e2e30; color: #efefef; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-danger { background: #c0392b; color: white; margin-top: 20px; width: 100%; padding: 14px; transition: background 0.2s; }
        .btn-danger:hover { background: #962d22; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9rem; }
        .alert-success { background: rgba(46, 204, 113, 0.15); border: 1px solid #2ecc71; color: #2ecc71; }
        .checkbox-group { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 10px; background: #161618; padding: 15px; border-radius: 8px; border: 1px solid #2c2c2e; }
        .checkbox-label { display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: #d1d1d6; cursor: pointer; }
        .danger-zone { margin-top: 50px; padding-top: 30px; border-top: 1px dashed #3a3a3c; text-align: center; }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="upload-container">
            
            <h1 style="margin: 0 0 10px 0; font-size: 1.8rem; font-weight: 800;"><?= $es_edicion ? 'Editar Asset' : 'Subir Assets' ?></h1>
            <p style="color: #8e8e93; margin-bottom: 35px; font-size: 0.95rem;">
                <?= $es_edicion ? 'Modifica la información registrada de tu asset para actualizar el catálogo público.' : 'Completa los detalles para publicar tu asset en el catálogo global.' ?>
            </p>

            <?php if(!empty($mensaje_exito)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($mensaje_exito) ?></div>
            <?php endif; ?>

            <form action="SubirAssets.php<?= $es_edicion ? '?editar_id='.$id_asset_editar : '' ?>" method="POST">
                
                <input type="hidden" name="id_asset" value="<?= $id_asset_editar ?>">
                
                <div class="form-group">
                    <label for="titulo">Título del Asset *</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" value="<?= htmlspecialchars($titulo) ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="5"><?= htmlspecialchars($descripcion) ?></textarea>
                </div>

                <div class="row-grid">
                    <div class="form-group">
                        <label for="precio">Precio ($ USD)</label>
                        <input type="number" id="precio" name="precio" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($precio) ?>">
                    </div>

                    <div class="form-group">
                        <label for="id_licencia">Licencia *</label>
                        <select id="id_licencia" name="id_licencia" class="form-control" required>
                            <option value="" disabled>Selecciona una licencia...</option>
                            <?php foreach($licencias as $licencia): ?>
                                <option value="<?= $licencia['id_licencia'] ?>" <?= $id_licencia_sel == $licencia['id_licencia'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($licencia['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="portadaurl">URL de la Imagen de Portada</label>
                    <input type="url" id="portadaurl" name="portadaurl" class="form-control" value="<?= htmlspecialchars($portadaurl) ?>">
                </div>

                <div class="form-group">
                    <label>Categorías</label>
                    <div class="checkbox-group">
                        <?php foreach($categorias as $cat): ?>
                            <?php $checked = in_array($cat['id_categoria'], $categorias_seleccionadas) ? 'checked' : ''; ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="categorias[]" value="<?= $cat['id_categoria'] ?>" <?= $checked ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Etiquetas Relacionadas</label>
                    <div class="checkbox-group">
                        <?php foreach($etiquetas as $etiq): ?>
                            <?php $checked = in_array($etiq['id_etiqueta'], $etiquetas_seleccionadas) ? 'checked' : ''; ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="etiquetas[]" value="<?= $etiq['id_etiqueta'] ?>" <?= $checked ?>>
                                <?= htmlspecialchars($etiq['nombre']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label>Estado de Visibilidad</label>
                    <div class="switch-container">
                        <label class="switch">
                            <input type="checkbox" name="activo" id="activo" <?= $activo == 1 ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                        <span id="switch-text" style="font-size: 0.9rem; color: #d1d1d6;">Asset visible y activo</span>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <?= $es_edicion ? 'Actualizar Cambios' : 'Subir Asset' ?>
                    </button>
                </div>
            </form>

            <?php if ($es_edicion): ?>
                <div class="danger-zone">
                    <h3 style="color: #e74c3c; margin: 0 0 5px 0; font-size: 1.1rem; font-weight: bold;">Zona de Peligro</h3>
                    <p style="color: #8e8e93; font-size: 0.85rem; margin: 0 0 15px 0;">Esta acción purgará el asset por completo y de forma irreversible del servidor.</p>
                    
                    <form action="SubirAssets.php" method="POST" onsubmit="return confirm('¿Estás absolutamente seguro de que deseas eliminar este asset? Esta acción no se puede deshacer.');">
                        <input type="hidden" name="id_asset" value="<?= $id_asset_editar ?>">
                        <button type="submit" name="accion_eliminar" class="btn btn-danger">Eliminar Asset Completo</button>
                    </form>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>