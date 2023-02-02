<?php
header('Content-Security-Policy: frame-ancestors https://admin.shopify.com https://'.$_GET['shop']);

/*
 * App home page
 */

session_id($_GET['session_id']);

require_once __DIR__."/config.php";
require_once __DIR__."/inc/functions.php";

$q_params = array();
$shop = $_SESSION['bemf_info']['shop'];
$access_token = $_SESSION['bemf_shop_data']['access_token'];

$q_params['limit'] = 50;
if(isset($_GET['page_info'])) {
    $q_params['page_info'] = $_GET['page_info'];
}

$page = (isset($_GET['page']))? $_GET['page'] : 1;

if(!empty($_POST)) {
    $all_data_saved = true;
    if(!empty($_POST['mg_products'])) {
        $mg_products = $_POST['mg_products'];
        foreach ($mg_products as $mg_product_id => $mg_product_metafields) {
            if(!empty($mg_product_metafields)) {
                foreach ($mg_product_metafields as $mg_product_metafield_id => $mg_product_metafield_value) {
                    $response = shopify_call(
                        $access_token,
                        str_replace(".myshopify.com", "", $shop),
                        "/admin/api/2022-10/products/{$mg_product_id}/metafields/{$mg_product_metafield_id}.json",
                        array(
                            'metafield' => array(
                                'id' => $id,
                                'value' => $mg_product_metafield_value,
                            )
                        ),
                        "PUT"
                    );
                    if(trim($response['headers']['status']) != 'HTTP/2 200') {
                        $all_data_saved = false;
                    }
                }
            }
        }
    }
}

$response = shopify_call(
    $access_token,
    str_replace(".myshopify.com", "", $shop),
    "/admin/api/2021-04/products.json",
    $q_params,
    "GET"
);

$products = json_decode($response['response'], TRUE);

$links = $response['headers']['link'];
$links = explode(',', $links);
if(!empty($links)) {
    foreach ($links as $key => $link) {
        $link = explode(';', $link);
        
        $url_components = parse_url($link[0]);
        parse_str($url_components['query'], $params);
        
        $type = str_replace('rel="', '', $link[1]);
        $type = str_replace('"', '', $type);
        $type = trim($type);
        
        $links[$key] = array(
            'href' => $link[0],
            'type' => $type,
            'page_info' => $params['page_info']
        );
    }
}
?>
<!doctype html>
<html>
    <head>
        <title>Home - Bulk Edit Metafields App</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
        
        <script src="<?php echo BASE_URL; ?>assets/js/jquery-2.2.3.min.js" type="text/javascript"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <script src="<?php echo BASE_URL; ?>assets/libs/ckeditor/ckeditor.js" type="text/javascript"></script>
        <style>
            .mg-m-tb-50 {
                margin-top: 50px;
                margin-bottom: 50px;
            }
        </style>
    </head>
    <body class="bg-white">
        <div class="container mg-m-tb-50">
            <h1 class="text-center">Bulk Edit Metafields</h1>
            <?php
            if(isset($all_data_saved)) {
                if($all_data_saved) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Data saved successfully.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                }
                else {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Some data is not saved please try again.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                }
            }
            ?>
            <form action="" method="POST">
                <hr/>
                <div class="well text-center">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
                <hr/>
                <div class="table-responsive">
                    <table class="table table-stripped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Metafields</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sn = (($page * 50) - 49);
                            if(!empty($products['products'])) {
                                foreach ($products['products'] as $product) {
                                    echo '<tr>
                                            <td>'.$sn.'</td>
                                            <td>'.$product['title'].'</td>
                                            <td>';
                                    $response = shopify_call(
                                        $access_token,
                                        str_replace(".myshopify.com", "", $shop),
                                        "/admin/api/2021-04/products/{$product['id']}/metafields.json",
                                        $q_params,
                                        "GET"
                                    );
                                    $metafields = json_decode($response['response'], TRUE);
                                    if(!empty($metafields['metafields'])) {
                                        foreach ($metafields['metafields'] as $metafield) {
                                            echo '<div>'.$metafield['key'].'</div>';
                                            echo '<input name="mg_products['.$product['id'].']['.$metafield['id'].']" type="text" value="'.$metafield['value'].'">';
                                        }
                                    }
                                    echo '</td>
                                        </tr>';
                                    $sn++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <hr/>
                <div class="well text-center">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
                <hr/>
            </form>
            <div class="well">
                <?php
                if(!empty($links)) {
                    foreach ($links as $link) {
                        if(isset($link['href']) && $link['href'] != '') {
                            if($link['type'] == 'next') {
                                echo '<a class="mg-p-next" href="?page='.($page + 1).'&page_info='.urlencode($link['page_info']).'">Next</a>';
                            }
                            else {
                                echo '<a class="mg-p-prev" href="?page='.($page - 1).'&page_info='.urlencode($link['page_info']).'">Prev</a>';
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
    </body>
</html>
