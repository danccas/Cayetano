<style>
.respuesta_seleccionada {
  background: #e8e8e8;
  padding: 5px;
  border-radius: 10px;
}
</style>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Ficha de Paciente</h1>
            <div>
            <a href="tel://<?= $paciente['telefono1'] ?>" class="d-none d-sm-inline-block btn btn-lg btn-success shadow-sm"><i class="fas fa-phone fa-sm text-white-50"></i> Llamar ahora</a>
<?php if(!empty($next)) { ?>
  <a href="/pacientes/llamar-<?= $next['id'] ?>" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">Siguiente</a>
<?php } ?>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-3">

              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
                </div>
                <div class="card-body">
<?php foreach($paciente as $k => $v) { if(in_array($k, ['id','notas'])) continue; ?>
<?php if($k == 'ultima') { ?>
<div style="border: 2px solid #4e73df;border-radius: 7px;text-align: center;padding: 5px;">
<b><?= strtoupper($k) ?> </b> <?= $v ?><br />
</div>
<?php } else { ?>
<b><?= strtoupper($k) ?> </b> <?= $v ?><br />
<?php } }  ?>
                </div>
              </div>
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Notas</h6>
                </div>
                <div class="card-body">
<div contenteditable="true" id="editor">
<?= nl2br($paciente['notas']) ?>
</div>
                </div>
              </div>

            </div>

            <div class="col-lg-9">
              <div class="card shadow mb-4">
                <div class="card-header py-3">
                  <h6 class="m-0 font-weight-bold text-primary">Cuestionario</h6>
                </div>
                <div class="card-body" style="text-align:center;">

<?php if(empty($formularios['INICIAL']) && false) { ?>
<div data-cuestionarios data-tipo="INICIAL" data-fecha="<?= date('Y-m-d') ?>">Espere...</div>
<?php } else { ?>
<ul class="nav nav-tabs" id="myNavTabs">
  <li class="active"><a href="#navtabs1" data-toggle="tab" class="btn">Cuestionario Inicial</a>
  <li><a href="#navtabs2" data-toggle="tab" class="btn">Cuestionario Diario</a>
  <li><a href="#navtabs3" data-toggle="tab" class="btn">Cuestionario Adicional</a>
</ul><br />
<div class="tab-content">
  <div class="tab-pane fade in active show" id="navtabs1">
    <div data-cuestionarios data-tipo="INICIAL" data-fecha="<?= date('Y-m-d') ?>">Espere...</div>
  </div>
  <div class="tab-pane fade" id="navtabs2">
    <div data-cuestionarios data-tipo="DIARIO" data-fecha="<?= date('Y-m-d') ?>">Espere...</div>
  </div>
  <div class="tab-pane fade" id="navtabs3">
    <div data-cuestionarios data-tipo="ADICIONAL" data-fecha="<?= date('Y-m-d') ?>">Espere...</div>
  </div>
</div>
<?php } ?>

                </div>
              </div>
            </div>
          </div>
<script>
setTimeout(function() {
  $("[data-cuestionarios]").each(function() {
    $(this).removeAttr('data-cuestionarios');
    Cuestionario({
      box: $(this),
      tipo: $(this).attr('data-tipo'),
      fecha: $(this).attr('data-fecha'),
    });
  });


  var before = '';
  $('#editor').on('focus', function() {
      before = $(this).html();
  }).on('blur keyup paste', function() { 
      if (before != $(this).html()) { $(this).trigger('change'); }
  });

  $('#editor').on('change', function() {
    console.log('text', $(this).text());
    $.ajax({
      url: '<?= Route::current() ?>campo',
      type: 'POST',
      data: { campo: 'notas', valor: $(this).html() },
      dataType: 'json',
    });
  });

}, 2000);
function respuesta(box, pf_id, pregunta_id, text) {
  console.log('RESPUESTA', box);
  $(box).closest('.row').find('.respuesta_seleccionada').removeClass('respuesta_seleccionada');
  $(box).closest('div').addClass('respuesta_seleccionada');
  $.ajax({
    url: '<?= Route::current() ?>guardar',
    type: 'POST',
    data: { pf_id: pf_id, pregunta_id: pregunta_id, text:  text },
    dataType: 'json',
    success: function(x) {

    },
  });
}
function Cuestionario(params) {
  $.ajax({
    url: '<?= Route::current() ?>cuestionario',
    type: 'POST',
    data: { tipo: params.tipo, fecha:  params.fecha },
    dataType: 'json',
    success: function(x) {
      if(!x.estado) {
        var rp = $('<a>').attr('href','#').attr('class', 'btn btn-primary btn-icon-split btn-lg');
        rp.html('<span class="icon text-white-50"><i class="fas fa-comment-dots"></i></span><span class="text">INICIAR CUESTIONARIO</span>');
        $(rp).on('click', function() {
          $.ajax({
            url: '<?= Route::current() ?>cuestionario',
            type: 'POST',
            data: { tipo: params.tipo, fecha:  params.fecha, click: 1 },
            success: function(x) {
              $(params.box).html(x);
            }
          });
        });
        $(params.box).html(rp);
      } else {
        $(params.box).html(x.html);
      }
    }
  });
}
</script>
