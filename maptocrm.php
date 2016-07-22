<?php
 
define('DEBUG', true);
define('PS_SHOP_PATH','http://www-dev.machineapub.com');
define('PS_WS_AUTH_KEY', '41BRIMTT1C601WSJ1EHP035TL7SWEZXH');

// Appel la librairie à la racine pour acceder aux méthodes GET, POST, PUT, DELETE
require_once('./PSWebServiceLibrary.php'); 

try
{
	//Création d'une nouvelle instance de PrestaShopWebService
	$webService = new PrestaShopWebservice(PS_SHOP_PATH, PS_WS_AUTH_KEY, DEBUG);
	//Création d'un tableau qui va contenir "customers"
	$opt['resource'] = 'customers';
	$optOrder['resource'] = 'orders';
	// Vérification si l'id est définit
	if (isset($_GET['id']))
	{
		$opt['id'] = (int)$_GET['id'];
		$optOrder['id_customer'] = (int)$_GET['id'];

	}
        $id_customer = isset($_GET['id'])?(int)$_GET['id']:0;
	//On récupère UN SEUL utilisateur ( en fonction de l'id choisit )
	$xml = $webService->get($opt);
	$resources = $xml->children()->children();
	$xml2 = $webService->get(array(
        'resource' => 'customers',
        'display' => '[id,id_lang,firstname,lastname,email,company]', //Les informations que l'ont veut obtenir
        'filter[id]'=>  $id_customer,  
        'limit' => 1 // Pour n'avoir qu'un seul résultat
	));
	$resources2 = $xml2->children()->children()->children();
        
        

    $xmlOrder = $webService->get($optOrder);
    $resourcesOrder = $xmlOrder->children()->children();
    //var_dump($resourcesOrder);
    $xmlOrderTab = $webService->get(array(
    	'resource' => 'orders',
    	'display' => '[id,id_customer,total_paid,reference]',
    	'filter[id_customer]'=>$id_customer
    	
    ));

    $resourcesOrder2 = $xmlOrderTab->children();
    //var_dump($resourcesOrder2);
}
catch (PrestaShopWebServiceExeption $ex)
{
	$trace = $ex->getTrace(); // Récupère toutes les Informations sur l'erreur
	$errorCode = $trace[0]['args'][0]; // Récupération du code d'erreur
	if ($errorCode == 401)
		echo 'Bad auth key'; else
		echo 'Other error : <br />'.$ex->getMessage();
	// Affiche un message associé à l’erreur
}

echo '<h1>Liste des utilisateurs ';
if (isset($_GET['id']))
{
	echo 'Details';
}
else
{ 
	echo 'List';
}
echo '</h1>';
// We set a link to go back to list if we are in customer's details
if (isset($_GET['id'])){
	echo '<a href="?">Retour</a>';
}
echo '<table border="5">';
//Si $resources est définie, on liste les éléments. Sinon, afficher erreur 
if (isset($resources))
{
	if (!isset($_GET['id']))
	{
		echo '<tr><th>Id</th><th>Détails</th></tr>';
		foreach ($resources as $resource)
		{
			
			echo '<tr><td>'.$resource->attributes().'</td><td>'.
			'<a href="?id='.$resource->attributes().'">Voir les détails</a>'.
			'</td></tr>';
		}
	}
	else
	{
		foreach ($resources2 as $key => $resource2)
		{
			echo '<tr>';
			echo '<th>'.$key.'</th><td>'.$resource2.'</td>';
			echo '</tr>';
		}
		foreach($resourcesOrder2->children() as $node_key=>$node){
                    echo '<tr>';
                    echo '<td>Commande Nu'.$node->id.'</td>';
                    foreach ($node->children() as $key2 => $resourceOrder2)
                    {
                            if($key2!='id')
                            echo '<td>'.$key2.':'.$resourceOrder2.'</td>';
                    }
                    echo '</tr>';
                }
	}
}
else
{
	echo 'erreur';
}
echo '</table>';

?>