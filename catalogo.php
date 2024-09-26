<?php
//var_dump($_GET);
if (isset($_GET['url_catalogo'])) {

    $url = $_GET['url_catalogo'];
    $queryCatalogo = 'SELECT subcategorias.ID, SUBCATEGORIA, CATEGORIA, IFNULL(IMAGEN_SUBCATEGORIA, "quienessomos.jpg") AS IMAGEN_SUBCATEGORIA , categorias.URL AS URL_CATEGORIA, subcategorias.URL AS URL_SUBCATEGORIA FROM subcategorias LEFT JOIN productos ON subcategorias.ID = productos.FK_SUBCATEGORIA LEFT JOIN categorias ON subcategorias.FK_CATEGORIA = categorias.ID WHERE subcategorias.URL = ? LIMIT 1;';
    $stmt = $db->prepare($queryCatalogo);

    if ($stmt->execute([$url])) {
        $infoCatalogo = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_catalogo = $infoCatalogo['ID'];

        $pagina_actual = isset($_GET['page']) ? $_GET['page'] : 1;
        $cantidad_pp = 36;

        $queryCantidad = "SELECT COUNT(productos.ID) AS CANTIDAD FROM productos LEFT JOIN subcategorias ON FK_SUBCATEGORIA = subcategorias.ID LEFT JOIN categorias ON subcategorias.FK_CATEGORIA = categorias.ID LEFT JOIN familias_productos ON productos.FK_FAMILIA = familias_productos.ID WHERE productos.FK_SUBCATEGORIA = ? AND productos.ACTIVO = 1 AND subcategorias.ESTADO = 1 ORDER BY productos.PRODUCTO, categorias.CATEGORIA, subcategorias.SUBCATEGORIA";
        $stmtCantidad = $db->prepare($queryCantidad);
        $stmtCantidad->execute([$id_catalogo]);
        $cantidad_filas = $stmtCantidad->fetch(PDO::FETCH_ASSOC);
        $cantidad_links = ceil($cantidad_filas['CANTIDAD'] / $cantidad_pp);

        if ($pagina_actual < 1) {
            $pagina_actual = 1;
        }
        if ($pagina_actual > $cantidad_links) {
            $pagina_actual = $cantidad_links;
        }
        $inicio = ($pagina_actual - 1) * $cantidad_pp;
        $number_of_page = ceil($cantidad_links / $cantidad_pp);
        $page_first_result = ($pagina_actual - 1) * $cantidad_pp;
    }
} else {
    header("Location: ../inicio/");
    exit;
}
if (is_null($infoCatalogo['URL_SUBCATEGORIA'])) {
?>
    <section class="bg-half-170 bg-light d-table w-100">
        <div class="container">
            <div class="row mt-5 justify-content-center">
                <h1>Catálogo no existente</h1>
                <h2>Por favor, vuelva al menú para seleccionarlo nuevamente.</h2>
            </div>
        </div>
    </section>
<?php
} else {
?>
    <!-- Hero Start -->
    <section class="bg-half-100 bg-light d-table w-100" style="background: #fff center / cover no-repeat no-repeat;">
        <div class="container">
            <div class="row mt-5 justify-content-center">
                <div class="col-lg-12 text-center">
                    <div class="pages-heading">
                        <h1 class="display-1 mt-5 mb-0"><?php dw($infoCatalogo['SUBCATEGORIA']) ?></h1>
                    </div>
                </div>
                <!--end col-->
            </div>
            <!--end row-->

            <div class="position-breadcrumb">
                <nav aria-label="breadcrumb" class="d-inline-block">
                    <ul class="breadcrumb bg-white rounded shadow mb-0 px-4 py-2">
                        <li class="breadcrumb-item"><a href="inicio/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="categoria/<?php dw($infoCatalogo['URL_CATEGORIA']) ?>"><?php dw($infoCatalogo['CATEGORIA']) ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php dw($infoCatalogo['SUBCATEGORIA']) ?></li>
                    </ul>
                </nav>
            </div>
        </div>
        <!--end container-->
    </section>
    <!--end section-->
    <div class="position-relative">
        <div class="shape overflow-hidden text-white">
            <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
            </svg>
        </div>
    </div>
    <!-- Hero End -->

    <!-- Start Products -->
    <section class="section pt-5">
        <div class="container">
            <h3 class="text-center">CATÁLOGO <?php dw($infoCatalogo['SUBCATEGORIA']) ?></h3>
            <h6 class="text-center text-danger">Para eliminar un producto del carrito seleccione la cantidad "0" en el selector de unidades o pack.</h6>

            <!-- PAGINATION START -->
            <div class="col-12 mt-4 pt-2" id="<?php dw($url) ?>">
                <ul class="pagination justify-content-center mb-0">

                    <?php
                    if ($cantidad_filas['CANTIDAD'] != 0) {
                        if ($pagina_actual != 1) {
                    ?>
                            <li class="page-item"><a class="page-link" href="catalogo/<?php dw($url) ?>/pagina/<?php dw($pagina_actual - 1) ?>/#<?php dw($url) ?>" aria-label="Next"><i class="mdi mdi-arrow-left"></i> Previa</a></li>
                        <?php
                        }
                        $limite = $pagina_actual + 2;
                        for ($i = 1; $i <= $cantidad_links; $i++) {
                            if ($i >= $pagina_actual && $i <= $limite) {
                                $estilo = $i == $pagina_actual ? 'active' : '';
                                echo "<li class='page-item $estilo'><a class='page-link' href='catalogo/$url/pagina/$i/#$url'>$i</a></li>";
                            }
                        }
                        if ($pagina_actual < $cantidad_links) {
                        ?>
                            <li class="page-item"><a class="page-link" href="catalogo/<?php dw($url) ?>/pagina/<?php dw($pagina_actual + 1) ?>/#<?php dw($url) ?>" aria-label="Next">Siguiente <i class="mdi mdi-arrow-right"></i></a></li>
                    <?php
                        }
                    }
                    ?>

                </ul>
            </div>
            <!--end col-->            
            <!-- PAGINATION END -->
            <div class="row" id="grid">

                <?php
                $queryProductos = 'SELECT productos.ID, CODIGO, productos.PRODUCTO, PRECIO, IFNULL(productos.IMAGEN, "logo-producto.jpg") AS IMAGEN , FAMILIA, categorias.CATEGORIA, subcategorias.SUBCATEGORIA, categorias.URL AS CATEGORIA_URL, subcategorias.URL AS SUBCATEGORIA_URL, CANTIDAD, OFERTA, MAS_VENDIDO, PACK, DESCRIPCION FROM productos LEFT JOIN subcategorias ON FK_SUBCATEGORIA = subcategorias.ID LEFT JOIN categorias ON subcategorias.FK_CATEGORIA = categorias.ID LEFT JOIN familias_productos ON productos.FK_FAMILIA = familias_productos.ID WHERE productos.FK_SUBCATEGORIA = ? AND productos.ACTIVO = 1 AND subcategorias.ESTADO = 1 ORDER BY productos.PRODUCTO, categorias.CATEGORIA, subcategorias.SUBCATEGORIA LIMIT ?, ?';

                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $stmt = $db->prepare($queryProductos);
                if ($stmt->execute([$id_catalogo, $inicio, $cantidad_pp])) {

                    while ($infoProducto = $stmt->fetch(PDO::FETCH_ASSOC)) {

                ?>
                        <div class="col-lg-3 col-md-6 col-12 mt-4 pt-2 picture-item">
                            <div class="card shop-list border-0 position-relative">
                                <ul class="label list-unstyled mb-0">
                                    <?php
                                    if ($infoProducto['OFERTA'] == 1) {
                                    ?>
                                        <li class="d-inline"><span class="badge rounded-pill bg-warning font_badge">Oferta</span></li>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if ($infoProducto['MAS_VENDIDO'] == 1) {
                                    ?>
                                        <li class="d-inline"><span class="badge rounded-pill bg-info font_badge">Más Vendido</span></li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                                <div class="shop-image position-relative overflow-hidden">
                                    <a href="javascript:void(0)" class="text-center modal_producto d-block" data-descripcion="<?php dw($infoProducto['DESCRIPCION']) ?>" data-oferta="<?php dw($infoProducto['OFERTA']) ?>" data-masvendido="<?php dw($infoProducto['MAS_VENDIDO']) ?>" data-imagen="<?php dw($infoProducto['IMAGEN']) ?>" data-producto="<?php dw($infoProducto['PRODUCTO']) ?>" data-bs-toggle="modal" data-bs-target="#modal_producto"><img src="images/productos/<?php dw($infoProducto['IMAGEN']) ?>" class="img-fluid imagen_producto" alt=""></a>
                                    <div>
                                        <div class="p-2 bg-soft-dark out-stock info_producto">
                                            <span class="text-dark product-name h6" title="<?php dw($infoProducto['PRODUCTO']) ?>">
                                                <?php
                                                if (strlen($infoProducto['PRODUCTO']) > 40) {
                                                    dw(substr($infoProducto['PRODUCTO'], 0, 40) . '...');
                                                } else {
                                                    dw($infoProducto['PRODUCTO']);
                                                }
                                                ?>
                                            </span>
                                            <div class="text-dark product-name my-1 d-flex justify-content-between align-items-center"><?php dw($infoProducto['CODIGO']) ?>
                                                <?php
                                                if (isset($_SESSION['CLIENTE']) && !empty($_SESSION['CLIENTE'])) {
                                                    if ($_SESSION['CLIENTE'] != "MINORISTA") {
                                                ?>
                                                        <span>$<?php dw($infoProducto['PRECIO']) ?></span>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <?php
                                            if (isset($_SESSION['CLIENTE']) && !empty($_SESSION['CLIENTE'])) {
                                                if ($_SESSION['CLIENTE'] != "MINORISTA") {
                                            ?>
                                                    <div class="d-flex justify-content-between align-items-center ms-2 pb-1 info_bottom_2">
                                                        <?php
                                                        $queryColor = "SELECT colores_productos.ID AS ID_COLORES, FK_PRODUCTOS, FK_COLORES, COLOR, CODIGO_COLOR FROM colores_productos LEFT JOIN colores ON colores_productos.FK_COLORES = colores.ID WHERE FK_PRODUCTOS = ?";
                                                        $stmt_color = $db->prepare($queryColor);
                                                        $stmt_color->execute([$infoProducto['ID']]);
                                                        if ($stmt_color->fetch(PDO::FETCH_ASSOC)) {

                                                        ?>
                                                            <h6 class="text-dark small mb-0">Elegir color:</h6>
                                                            <select id="producto_color<?php dw($infoProducto['ID']) ?>" class="form-select form-control select_color">
                                                                <option data-color_option="" data-id_colores_productos_option="" data-id_producto="<?php dw($infoProducto['ID']) ?>" data-codigo_producto="<?php dw($infoProducto['CODIGO']) ?>" data-codigo_color="" value="" class="ver_colores">Color | Modelo</option>
                                                                <?php
                                                                $queryColoresProductos = 'SELECT colores_productos.ID AS ID_COLORES, FK_PRODUCTOS, FK_COLORES, COLOR, CODIGO_COLOR FROM colores_productos LEFT JOIN colores ON colores_productos.FK_COLORES = colores.ID WHERE FK_PRODUCTOS = ? ORDER BY colores.ORDEN_COLOR ASC';

                                                                $stmt_col_prod = $db->prepare($queryColoresProductos);

                                                                if ($stmt_col_prod->execute([$infoProducto['ID']])) {

                                                                    while ($infoColoresProductos = $stmt_col_prod->fetch(PDO::FETCH_ASSOC)) {

                                                                ?>

                                                                        <option value="<?php dw($infoColoresProductos['COLOR']) ?>" data-color_option="<?php dw($infoColoresProductos['COLOR']) ?>" data-id_colores_productos_option="<?php dw($infoColoresProductos['ID_COLORES']) ?>" data-id_producto="<?php dw($infoProducto['ID']) ?>" data-codigo_producto="<?php dw($infoProducto['CODIGO']) ?>" data-codigo_color="<?php dw($infoColoresProductos['CODIGO_COLOR']) ?>"><?php dw($infoColoresProductos['COLOR']) ?></option>
                                                                <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center info_bottom">

                                                        <select class="form-select form-control mb-1 select_producto select_id_<?php dw($infoProducto['ID']) ?>" data-id_producto="<?php dw($infoProducto['ID']) ?>" data-descripcion="<?php dw($infoProducto['DESCRIPCION']) ?>" data-precio="<?php dw($infoProducto['PRECIO']) ?>" data-familia="<?php dw($infoProducto['FAMILIA']) ?>" data-cantidad_select="<?php dw($infoProducto['CANTIDAD']) ?>" data-pack="<?php dw($infoProducto['PACK']) ?>" data-codigo="<?php dw($infoProducto['CODIGO']) ?>" data-nombre="<?php dw($infoProducto['PRODUCTO']) ?>" data-id_colores_productos="" data-color="" data-codigo_color="" data-imagen="<?php dw($infoProducto['IMAGEN']) ?>">
                                                            <option value="0">Unidad</option>
                                                            <option value="0">0 unidades</option>
                                                            <?php
                                                            $id = $infoProducto['CODIGO'];
                                                            $usuario = $_SESSION['ID'];

                                                            $queryCantidades = "SELECT QUANTITY FROM pedidos_pendientes WHERE FK_CLIENTE = ? AND ID_PRODUCTO = ? LIMIT 1;";

                                                            $stmt_cantidad = $db->prepare($queryCantidades);
                                                            $stmt_cantidad->execute([$usuario, $id]);
                                                            $infoCantidades = $stmt_cantidad->fetch(PDO::FETCH_ASSOC);

                                                            if (isset($infoCantidades['QUANTITY'])) {
                                                                $cantidades = $infoCantidades['QUANTITY'];
                                                            }

                                                            if ($infoProducto['CANTIDAD']) {
                                                                for ($i = 1; $i <= $infoProducto['CANTIDAD']; $i++) {
                                                                    if (isset($cantidades)) {
                                                                        if ($cantidades == $i) {
                                                                            echo '<option value="' . $i . '" selected>' . $i . ' unidades</option>';
                                                                        } else {
                                                                            echo '<option value="' . $i . '">' . $i . ' unidades</option>';
                                                                        }
                                                                    } else {
                                                                        echo "<option value='" . $i . "'>" . $i . " unidades</option>";
                                                                    }
                                                                }
                                                                $cantidades = false;
                                                            }
                                                            ?>
                                                        </select>
                                                        <select class="form-select form-control mb-1 select_pack select_pack_id_<?php dw($infoProducto['ID']) ?>" data-id_producto="<?php dw($infoProducto['ID']) ?>" data-descripcion="<?php dw($infoProducto['DESCRIPCION']) ?>" data-precio="<?php dw($infoProducto['PRECIO']) ?>" data-familia="<?php dw($infoProducto['FAMILIA']) ?>" data-cantidad_select="<?php dw($infoProducto['CANTIDAD']) ?>" data-pack="<?php dw($infoProducto['PACK']) ?>" data-codigo="<?php dw($infoProducto['CODIGO']) ?>" data-nombre="<?php dw($infoProducto['PRODUCTO']) ?>" data-id_colores_productos="" data-color="" data-codigo_color="" data-imagen="<?php dw($infoProducto['IMAGEN']) ?>">
                                                            <option value="0">Por Pack</option>
                                                            <option value="0">0 Pack</option>
                                                            <?php
                                                            $id = $infoProducto['CODIGO'] . "_PACK";
                                                            $usuario = $_SESSION['ID'];

                                                            $queryCantidades = "SELECT QUANTITY FROM pedidos_pendientes WHERE FK_CLIENTE = ? AND ID_PRODUCTO = ? LIMIT 1;";

                                                            $stmt_cantidad = $db->prepare($queryCantidades);
                                                            $stmt_cantidad->execute([$usuario, $id]);
                                                            $infoCantidades = $stmt_cantidad->fetch(PDO::FETCH_ASSOC);

                                                            if (isset($infoCantidades['QUANTITY'])) {
                                                                $cantidades = $infoCantidades['QUANTITY'];
                                                            }
                                                            if ($infoProducto['PACK']) {
                                                                for ($i = 1; $i <= 20; $i++) {
                                                                    $total_pack = $i * $infoProducto['PACK'];

                                                                    if (isset($cantidades)) {
                                                                        if ($cantidades == $total_pack) {
                                                                            echo "<option value='" . $total_pack . "' selected>" . $i . " Pack (" . $total_pack . ")</option>";
                                                                        } else {
                                                                            echo "<option value='" . $total_pack . "'>" . $i . " Pack (" . $total_pack . ")</option>";
                                                                        }
                                                                    } else {
                                                                        echo "<option value='" . $total_pack . "'>" . $i . " Pack (" . $total_pack . ")</option>";
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                            <?php
                                                }
                                            }
                                            ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end col-->
                <?php
                    }
                }
                ?>
            </div>
            <!--end row-->
            <!-- PAGINATION START -->
            <div class="col-12 mt-4 pt-2">
                <ul class="pagination justify-content-center mb-0">

                    <?php
                    if ($cantidad_filas['CANTIDAD'] != 0) {
                        if ($pagina_actual != 1) {
                    ?>
                            <li class="page-item"><a class="page-link" href="catalogo/<?php dw($url) ?>/pagina/<?php dw($pagina_actual - 1) ?>/#<?php dw($url) ?>" aria-label="Back"><i class="mdi mdi-arrow-left"></i> Previa</a></li>
                        <?php
                        }
                        $limite = $pagina_actual + 2;
                        for ($i = 1; $i <= $cantidad_links; $i++) {
                            if ($i >= $pagina_actual && $i <= $limite) {
                                $estilo = $i == $pagina_actual ? 'active' : '';
                                echo "<li class='page-item $estilo'><a class='page-link' href='catalogo/$url/pagina/$i/#$url'>$i</a></li>";
                            }
                        }
                        if ($pagina_actual < $cantidad_links) {
                        ?>
                            <li class="page-item"><a class="page-link" href="catalogo/<?php dw($url) ?>/pagina/<?php dw($pagina_actual + 1) ?>/#<?php dw($url) ?>" aria-label="Next">Siguiente <i class="mdi mdi-arrow-right"></i></a></li>
                    <?php
                        }
                    }
                    ?>

                </ul>
            </div>
            <!--end col-->
            <div class="d-flex flex-wrap pt-5 gap-3">
                
                <?php
                $queryFamilias = 'SELECT subcategorias.ID, SUBCATEGORIA, CATEGORIA, IFNULL(IMAGEN_SUBCATEGORIA, "quienessomos.jpg") AS IMAGEN_SUBCATEGORIA , categorias.URL AS URL_CATEGORIA, subcategorias.URL AS URL_SUBCATEGORIA, familias_productos.URL AS FAMILIAS_URL, FAMILIA FROM subcategorias LEFT JOIN productos ON subcategorias.ID = productos.FK_SUBCATEGORIA LEFT JOIN categorias ON subcategorias.FK_CATEGORIA = categorias.ID LEFT JOIN familias_productos ON productos.FK_FAMILIA = familias_productos.ID WHERE subcategorias.ID = ? GROUP BY FAMILIA ORDER BY FAMILIA';

                $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                $stmt_familias = $db->prepare($queryFamilias);
                if ($stmt_familias->execute([$id_catalogo])) {

                    while ($infoFamilia = $stmt_familias->fetch(PDO::FETCH_ASSOC)) {

                ?>
                        <a href="linea/<?php dw($infoFamilia['URL_SUBCATEGORIA']) ?>/<?php dw($infoFamilia['FAMILIAS_URL']) ?>/#<?php dw($infoFamilia['URL_SUBCATEGORIA']) ?>" class="btn btn-soft-secondary px-2 py-1"><?php dw($infoFamilia['FAMILIA']) ?></a>
                <?php
                    }
                }
                ?>
            </div>
            <!-- PAGINATION END -->
        </div>
        <!--end container-->
    </section>
    <!--end section-->
    <!-- End Products -->
<?php
}
?>
