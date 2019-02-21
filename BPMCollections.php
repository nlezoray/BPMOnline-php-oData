<?php
namespace AppBundle\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Psr\Log\LoggerInterface;
class BPMCollections 
{    
    /*
     * AUTHENTIFICATION: Création du cookie de connexion
     */
    public function __construct() {
        $target_url = 'https://urlto.bpmonline.com/ServiceModel/AuthService.svc/Login';
        $verbose = fopen('C:\\workspace\\apitest\\var\\logs\\BPMInterfaces.log', 'w+');
        
        $post = array (
            'user'=> 'UserName',
            'password' => 'Password',
            'IsDebug'=> false
        );
        $post_string = json_encode($post);
        
        $ch = curl_init ($target_url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'C:\\Users\\Public\\cookieBPM.txt'); //sauvegarde du cookie
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($post_string))
            );
        
        $result = curl_exec($ch);
        curl_close ($ch);
    }
    
    /*******************************************************************************CRUD*******************************************************************************************************/
    /* CREATE
     * Requête OData en POST sur BPMOnline
     * $contactObject = array("Name" => (string)$data->contact->Name,
     *                        "Phone" => (string)$data->contact->Phone,
     *                        "Email" => (string)$data->contact->Email,
     *                        "JobTitle" => (string)$data->contact->JobTitle);
     */
    public function WSPOSTBPM($collection, $Id='', $tabObject) {
        $lines = file('C:\\Users\\Public\\cookieBPM.txt');
        $token = trim(substr($lines[6], strpos($lines[6], "BPMCSRF") + strlen("BPMCSRF")));
        
        $target_url = "https://urlto.bpmonline.com/0/ServiceModel/EntityDataService.svc/".$collection
        
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\\Users\\Public\\cookieBPM.txt'); //lecture du cookie
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tabObject));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;odata=verbose',
            'Accept: application/json;odata=verbose',
            'BPMCSRF:' .  $token,
            'Content-Length: ' . strlen(json_encode($tabObject))));
        curl_setopt($ch, CURLOPT_URL, $target_url);
        
        $result = curl_exec($ch);
        curl_close ($ch);
        
        return $result;
    }
    
    /* READ
     * Requête OData en GET sur BPMOnline
     * $api_query: array dont le contenu des éléments est encodé en URL
     */
    public function WSGETBPM($collection='', $api_query) {
        
        $lines = file('C:\\Users\\Public\\cookieBPM.txt');
        $token = trim(substr($lines[6], strpos($lines[6], "BPMCSRF") + strlen("BPMCSRF")));
        
        $target_url = 'https://urlto.bpmonline.com/0/ServiceModel/EntityDataService.svc/'.$collection;
        if (count($api_query) > 0) {
            $target_url .= '?' . implode('&', array_map(function($item) {
                return $item[0] . '=' . $item[1];
            }, array_map(null, array_keys($api_query), $api_query)));
        }
                
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\\Users\\Public\\cookieBPM.txt'); //lecture du cookie
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;odata=verbose',
            'Accept: application/json;odata=verbose',
            'BPMCSRF:' .  $token));
        curl_setopt($ch, CURLOPT_URL, $target_url);
        
        $result = curl_exec($ch);
        curl_close ($ch);
        
        return $result;
    }
    
    /* UPDATE
     * Requête OData en PUT sur BPMOnline
     * Request string:
     * POST <BPMonline application address>/0/ServiceModel/EntityDataService.svc/ContactCollection/
     * Attends l'Id à mettre à jour, Et l'Objet à mettre à jour de la collection
     * exemple $contactObject = array("Name" => (string)$data->contact->Name,
     *                                "Phone" => (string)$data->contact->Phone,
     *                                "Email" => (string)$data->contact->Email,
     *                                "JobTitle" => (string)$data->contact->JobTitle);
     */
    public function WSPUTBPM($collection, $Id='', $tabObject) {
        $lines = file('C:\\Users\\Public\\cookieBPM.txt');
        $token = trim(substr($lines[6], strpos($lines[6], "BPMCSRF") + strlen("BPMCSRF")));
        
        $target_url = "https://urlto.bpmonline.com/0/ServiceModel/EntityDataService.svc/".$collection."(guid'".$Id."')";
        
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\\Users\\Public\\cookieBPM.txt'); //lecture du cookie
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($tabObject));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;odata=verbose',
            'Accept: application/json;odata=verbose',
            'BPMCSRF:' .  $token,
            'Content-Length: ' . strlen(json_encode($tabObject))));
        curl_setopt($ch, CURLOPT_URL, $target_url);
        
        $result = curl_exec($ch);
        curl_close ($ch);
        
        return $result;
    }
    
    /* DELETE
     * Requête OData en DELETE sur BPMOnline
     * Request string:
     * POST <BPMonline application address>/0/ServiceModel/EntityDataService.svc/ContactCollection/
     * Attends l'Id à supprimer
     */
    public function WSDELETEBPM($collection, $Id, $tabCollection = null) {
        $lines = file('C:\\Users\\Public\\cookieBPM.txt');
        $token = trim(substr($lines[6], strpos($lines[6], "BPMCSRF") + strlen("BPMCSRF")));
        
        $target_url = "https://urlto.bpmonline.com/0/ServiceModel/EntityDataService.svc/".$collection."(guid'".$Id."')";
        
        $ch = curl_init ();
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'C:\\Users\\Public\\cookieBPM.txt'); //lecture du cookie
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json;odata=verbose',
            'Accept: application/json;odata=verbose',
            'BPMCSRF:' .  $token));
        curl_setopt($ch, CURLOPT_URL, $target_url);
        
        $result = curl_exec($ch);
        curl_close ($ch);
        
        return $result;
    }
    /***************************************************************************FIN*CRUD*******************************************************************************************************/    
}
