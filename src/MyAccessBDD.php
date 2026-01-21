<?php
include_once("AccessBDD.php");
/**
 * Description of MyAccessBDD
 *
 * @author Karl
 */
class MyAccessBDD extends AccessBDD {
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    #[\Override]
    protected function traitementDelete(string $table, ?array $champs): ?int {
        switch($table){
            case "nettoie_gamme" :
                return $this->deleteNettoieGamme();
            case "" :
                // return $this->uneFonction(parametres);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }
    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */
    #[\Override]
    protected function traitementInsert(string $table, ?array $champs): ?int {
        switch($table){
            case "" :
                // return $this->uneFonction(parametres);
            case "produit" :
                return $this->insertProduit($champs);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    #[\Override]
    protected function traitementSelect(string $table, ?array $champs): ?array {
        switch($table){
            case "" :
                // return $this->uneFonction(parametres);
            case "variantes" :
                return $this->selectVariantes();
            case "intolerance" :
                return $this->selectIntolerance($champs);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }
    }
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    #[\Override]
    protected function traitementUpdate(string $table, ?string $id, ?array $champs): ?int {
        switch($table){
            case "" :
                // return $this->uneFonction(parametres);
            case "transfert_images" :
                return $this->updateCheminImges($champs);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }
    }
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	 
            return $this->conn->queryBDD($requete, $champs);
        }
    }	
    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }
    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
    /**
     * récupère les produits (id, nom) avec le nombre de variantes (détailsà par produit
     * @return array|null
     */
    private function selectVariantes() : ?array {
        $req = "select p.id, p.nom, count(*) as 'variantes' ";		
        $req .= "from produit p left join details_produits dp on p.id = dp.idproduit ";		
        $req .= "group by p.id, p.nom ";		
        return $this->conn->queryBDD($req);               
    }    
    /**
     * récupère les produits (id, nom, description, détails) dont un ingrédient n'est présent
     * ni dans la description, ni dans les détails
     * @param array|null $champs
     * @return array|null
     */
    private function selectIntolerance(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists("ingredient", $champs)){
            return null;
        }               
        $req = "select p.id, p.nom, p.description, dp.details ";		
        $req .= "from produit p left join details_produits dp on p.id = dp.idproduit ";		
        $req .= "where not (p.description like :ingredient or dp.details like :ingredient)";		
        $champsNecessaires["ingredient"] = '%' . $champs["ingredient"] . '%'; 
        return $this->conn->queryBDD($req, $champsNecessaires);               
    }
    /**
     * insère un prodiot et une gamme si idgamme du produit n'existe pas
     * @param array|null $champs
     * @return int|null nombre total de lignes insérées
     */
    private function insertProduit(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists("idgamme", $champs)){
            return null;
        }        
        // construction de la requête
        $req = "insert into gamme (id) ";		 
        $req .= "select (:id) from dual ";		 
        $req .= "where not exists (select * from gamme where id = :id);";
        $champsNecessaires["id"] = $champs["idgamme"];
        $nbInsertGamme = $this->conn->updateBDD($req, $champsNecessaires); 
        if ($nbInsertGamme === null){
            return null;
        }else{
            return $this->insertOneTupleOneTable("produit", $champs) + $nbInsertGamme;
        }
    }
    /**
     * dans la table produit, change le chemin des images
     * @param array|null $champs
     * @return int|null nombre de lignes modifiées ou null si erreur
     */
    private function updateCheminImges(?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists("ancien", $champs) || !array_key_exists("nouveau", $champs)){
            return null;
        }
        $req = "update produit ";
        $req .= "set urlimg = replace(urlimg, :ancien, :nouveau);";
        $champsNecessaires["ancien"]=$champs["ancien"];
        $champsNecessaires["nouveau"]=$champs["nouveau"];
        return $this->conn->updateBDD($req, $champsNecessaires);
    }
    /**
     * nettoie la table gamme
     * en supprimant les lignes dont le libelle et le picto sont vides
     * et dont l'id n'est pas utilisé par un prouit
     * @return int|null nombre de lignes supprimées
     */
    private function deleteNettoieGamme() : ?int {
        $req = "delete from gamme ";
        $req .= "where libelle = '' and picto = '' ";
        $req .= "and id not in (select idgamme from produit);";
        return $this->conn->updateBDD($req);
    }  
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(Exception $e){
            throw $e;
        }
    }
}