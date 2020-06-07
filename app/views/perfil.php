          <h1 class="h3 mb-2 text-gray-800">Mis datos</h1>
          <p class="mb-4">Complete su información personal.</p>

          <div class="card shadow mb-4">
            <div class="card-header py-3">
              <h6 class="m-0 font-weight-bold text-primary">Formulario de información</h6>
            </div>
            <div class="card-body">
                <?= Formity::getInstance('perfil')->render(); ?>
            </div>
          </div>
