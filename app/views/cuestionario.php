<div class="col-lg-12">
<?php foreach($preguntas as $k => $p) { ?>
  <div id="preg<?= $k ?>" class="card mb-4 py-3 border-left-primary xpregunta" data-pf="<?= $pf_id ?>" data-pregunta="<?= $p['id'] ?>">
    <div style="color: #cecece;position: absolute;font-size: 25px;left: 25px;top: 10px;"><?= $k + 1 ?></div>
    <div class="card-body">
      <div class="h5 mb-0 font-weight-bold text-gray-800" style="margin-bottom: 10px!important;">
        <?= $p['texto'] ?>
      </div>
<?php if($p['tipo'] == 'UNO' || $p['tipo'] == 'TRES') { ?>
<div class="row" style="max-width:400px;margin:0 auto;text-align:center;">
<div class="col-lg-6<?= $p['respuesta'] == 'SI' ? ' respuesta_seleccionada' : '' ?>">
<a href="javascript:void(0)" onclick="respuesta(this, <?= $pf_id ?>, <?= $p['id'] ?>, 'SI');" class="btn btn-success btn-circle">
  <i class="fas fa-check"></i>
</a>
</div>
<div class="col-lg-6<?= $p['respuesta'] == 'NO' ? ' respuesta_seleccionada' : '' ?>">
<a href="javascript:void(0)" onclick="respuesta(this, <?= $pf_id ?>, <?= $p['id'] ?>, 'NO');" class="btn btn-danger btn-circle">
  <i class="fas fa-times"></i>
</a>
</div>
</div>
<?php } elseif($p['tipo'] == 'DOS') { ?>
<input class="form-control form-control-lg" type="text" onchange="respuesta(this, <?= $pf_id ?>, <?= $p['id'] ?>, this.value);" value="<?= $p['respuesta'] ?>" style="text-align: center;">
<?php } ?>
    </div>
  </div>
<?php } ?>
</div>

