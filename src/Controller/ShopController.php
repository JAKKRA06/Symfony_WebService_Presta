<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\PrestaShopWebservice;


class ShopController extends AbstractController
{
    /**
     * @Route("/shop", name="shop")
     */
    public function index()
    {
      define('DEBUG', true);
      define('PS_SHOP_PATH', 'localhost/Presta_shop_aplication/');
      define('PS_WS_AUTH_KEY', '1WTH1KDBTTQLTXRAGSMGVJFQD7NND5WE');


      try
      {
      	$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
      	$opt = array('resource' => 'products', 'display' => '[name, value]'); //products
      	if(isset($_GET['id'])) {
      		$opt['id'] = $_GET['id'];
      	}
        $xml = $webService->get($opt);
        $resources = $xml->children()->children();

      }
      catch (PrestaShopWebserviceException $e)
      {
      	$trace = $e->getTrace();
      	if ($trace[0]['args'][0] == 404) echo 'Bad ID';
      	else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
      	else echo 'Other error<br />'.$e->getMessage();
      }


      return $this->render('index.html.twig', [
        'resources' => $resources,
      ]);

    }
}
