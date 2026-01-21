<?php
include_once("MyAccessBDD.php");
/**
 * Description of Controle
 *
 * @author Karl
 */
class Controle {
    /**
     * 
     * @var MyAccessBDD
     */
    private $myAaccessBDD;
    /**
     * réponse renvoyée (affichée) au client au format json
     * @param int $code code standard HTTP (200, 500, ...)
     * @param string $message message correspondant au code
     * @param array|int|string|null $result
     */
    private function reponse(int $code, string $message, array|int|string|null $result=""){
        $retour = array(
            'code' => $code,
            'message' => $message,
            'result' => $result
        );
        echo json_encode($retour, JSON_UNESCAPED_UNICODE);
    }
    /**
     * contrôle si le résultat n'est pas null
     * demande l'affichage de la réponse adéquate
     * @param array|int|null $result résultat de la requête
     */
    private function controleResult(array|int|null $result) {
        if (!is_null($result)){
            $this->reponse(200, "OK", $result);
        }else{	
            $this->reponse(400, "requete invalide");
        }        
    }
    /**
     * Constructeur : récupère l'instance d'accès à la BDD
     */
    public function __construct(){
        try{
            $this->myAaccessBDD = new MyAccessBDD();
        }catch(Exception $e){
            $this->reponse(500, "erreur serveur");
            die();
        }
    }
    /**
     * authentification incorrecte
     */
    public function unauthorized(){
        $this->reponse(401, "authentification incorrecte");
    }
    /**
     * réception d'une demande de requête
     * demande de traiter la requête puis demande d'afficher la réponse
     * @param string $methodeHTTP
     * @param string $table
     * @param string|null $id
     * @param array|null $champs
     */
    public function demande(string $methodeHTTP, string $table, ?string $id, ?array $champs){
        $result = $this->myAaccessBDD->demande($methodeHTTP, $table, $id, $champs);
        $this->controleResult($result);
    }
}