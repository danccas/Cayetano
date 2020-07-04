<!DOCTYPE html>
<html lang="es-PE">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Cayetano</title>
  <!-- Custom fonts for this template -->
  <link href="/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
  <link href="/css/sb-admin-2.min.css" rel="stylesheet">
  <link href="/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
  <style>
    .help {
      color: red;
      font-size: 12px;
      padding: 5px;
    }
  </style>

</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <?php include(Route::g()->attr('views') . 'internal.nav.php'); ?>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">
        <?php include(Route::g()->attr('views') . 'internal.header.php'); ?>

        <!-- Begin Page Content -->
        <div class="container-fluid">
<?php if(Route::hasTitle() || Route::hasDescription() || !empty(Route::data('submenu'))) { ?>
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
          <div>
          <h1 class="h3 mb-0 text-gray-800"><?= Route::getTitle() ?></h1>
          <?php if(Route::hasDescription()) { ?><p class="mb-4"><?= Route::getDescription(); ?></p><?php } ?>
          </div>
          <div>
<?php if(!empty(Route::data('submenu'))) { foreach(Route::nav() as $nav) { ?>
            <a href="<?= $nav['link'] ?>" <?= (!empty($nav['popy']) ? 'data-popy' : '') ?> class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
            <!-- <i class="fas fa-phone fa-sm text-white-50"></i> -->
            <?= $nav['nombre'] ?></a>
<?php } } ?>
          </div>
          </div>
<?php } ?>
          <?php if(isset($VISTA_HTML)) { echo $VISTA_HTML; } else { include(Route::g()->attr('views') . $VISTA . '.php'); } ?>
        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

<?php include(Route::g()->attr('views') . 'internal.footer.php'); ?> 

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Bootstrap core JavaScript-->
  <script src="/vendor/jquery/jquery.min.js"></script>
  <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="/vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="/js/sb-admin-2.min.js"></script>

  <!-- Page level plugins -->
  <script src="/vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="/vendor/datatables/dataTables.bootstrap4.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="/js/demo/datatables-demo.js"></script>

  <!-- Page level plugins -->
  <script src="/vendor/chart.js/Chart.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="/js/demo/chart-area-demo.js"></script>
  <script src="/js/demo/chart-pie-demo.js"></script>

</body>

</html>
