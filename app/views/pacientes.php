          <!-- Page Heading -->
          <h1 class="h3 mb-2 text-gray-800">Pacientes</h1>
          <p class="mb-4">Se muestra la relación de pacientes registrados.</p>

          <!-- DataTales Example -->
          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Relación de Pacientes</h6>
            </div>
            <div class="card-body">
              <div class="table-responsive">
<?= Tablefy::getInstance('listado')->render(array()); ?>
              </div>
            </div>
          </div>
