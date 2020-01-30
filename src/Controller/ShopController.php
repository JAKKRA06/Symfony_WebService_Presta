<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Utils\PrestaShopWebservice;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ShopTypeForm;
class ShopController extends AbstractController
{
    /**
     * @Route("/", name="list")
     */
    public function list(Request $request)
    {
        define('DEBUG', false);
        define('PS_SHOP_PATH', 'localhost/Presta_shop_aplication/');
        define('PS_WS_AUTH_KEY', '1WTH1KDBTTQLTXRAGSMGVJFQD7NND5WE');
        try
        {
            $webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
            $opt = array(
                'resource' => 'products',
                'display' => '[id, name, price]',
                'limit' => '1'
            );
            $xml = $webService->get($opt);
            $resources = $xml->children()
                ->children();
        }
        catch(PrestaShopWebserviceException $e)
        {
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) echo 'Bad ID';
            else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
            else echo 'Other error<br />' . $e->getMessage();
        }
        $quantity = $this->getQuantity($webService, $request);
        $form = $this->createForm(ShopTypeForm::class);
        $form->handleRequest($request);
        return $this->render('index.html.twig', ['resources' => $resources, 'quantities' => $quantity, 'id' => $request
            ->query
            ->get('id') , 'form' => $form->createView() , ]);
    }
    //funkcja zwraca ilosc sztuk oraz ma wykonywac update->cos nie dziala jednak
    protected function getQuantity(PrestaShopWebservice $webService, Request $request)
    {
        try
        {
            $opt = array(
                'resource' => 'stock_availables',
                'display' => '[quantity]',
                'limit' => '1'
            );
            $xml = $webService->get($opt);
            $quantity = $xml->children()
                ->children();
        }
        catch(PrestaShopWebserviceException $e)
        {
            $trace = $e->getTrace();
            if ($trace[0]['args'][0] == 404) echo 'Bad ID';
            else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
            else echo 'Other error<br />' . $e->getMessage();
        }
        $form = $this->createForm(ShopTypeForm::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $newQuantity = $form['quantity']->getData();
            foreach ($quantity as $nodeKey => $node)
            {
                if ($nodeKey == $newQuantity)
                {
                    $quantity->$nodeKey = $newQuantity;
                }
            }
            try
            {
                $opt = array(
                    'resource' => 'stock_availables'
                );
                $opt['putXml'] = $xml->asXML();
                $opt['id'] = 1;
                $xml = $webService->edit($opt);
                echo "Successfully updated.";
            }
            catch(PrestaShopWebserviceException $ex)
            {
                $trace = $ex->getTrace();
                if ($trace[0]['args'][0] == 404) echo 'Bad ID';
                else if ($trace[0]['args'][0] == 401) echo 'Bad auth key';
                else echo 'Other error<br />' . $ex->getMessage();
            }
        }
        return $quantity;
    }
}
