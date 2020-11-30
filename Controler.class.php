<?php
/**
 * Class Controler
 * Gère les requêtes HTTP
 * 
 * @author Jonathan Martel
 * @version 1.0
 * @update 2019-01-21
 * @license Creative Commons BY-NC 3.0 (Licence Creative Commons Attribution - Pas d’utilisation commerciale 3.0 non transposé)
 * @license http://creativecommons.org/licenses/by-nc/3.0/deed.fr
 * 
 */

class Controler 
{
	
		/**
		 * Traite la requête
		 * @return void
		 */
		public function gerer()
		{
			switch ($_GET['requete']) {
				case 'authentification':
					$this->authentification();
					break;
				case 'listeBouteille':
					$this->listeBouteille();
					break;
				case 'autocompleteBouteille':
					$this->autocompleteBouteille();
					break;
				case 'ajouterNouvelleBouteilleCellier':
					$this->ajouterNouvelleBouteilleCellier();
					break;
				case 'ajouterBouteilleCellier':
					$this->ajouterBouteilleCellier();
					break;
				case 'boireBouteilleCellier':
					$this->boireBouteilleCellier();
					break;
				case 'formModificationBtl':
				 	$this->formModificationBtl();
					 break;
				case 'sauvegardeBouteille':
					$this->sauvegardeBouteille();
					break;
				case 'supprimerBouteilleCellier':
					$this->supprimerBouteilleCellier();
					break;
				case 'accueilUsager':
					$this->accueilUsager();
				    break;
				case 'modifierCompte':
					$this->modifierCompte();
					break;
				case 'creerCompte':
					$this->creerCompte();
					break;
				case 'sauvegardeCompte':
					$this->sauvegardeCompte();
					break;
				case 'deconnexion':
					$this->deconnexion();
					break;
				default:
					$this->accueil();
					break;
			}
		}

		private function authentification()
		{
			$auth = new Authentification();
			$body = json_decode(file_get_contents('php://input'));

			//validations back end
			if(isset($body->courriel) && isset($body->password) && !empty(trim($body->courriel)) && !empty(trim($body->password))){

				// test regex
				// $regexCourriel = '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/i';
				$regexCourriel = '/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/i';
				$regexPassword = '/^(?=.*[0-9])(?=.*[a-z])([a-z0-9!@#$%^&*;.,\-_\'"]{4,})$/i';

				if (preg_match($regexCourriel, $body->courriel) != 0 && preg_match($regexPassword, $body->password) != 0){

					$valide = $auth->validerAuthentification($body->courriel, $body->password);

					if($valide){
						// sauvegarde de l'usager authentifié
						$_SESSION["courriel"] = $body->courriel;

						$responseObj = new stdClass();
						$responseObj->success = true;
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}else{
						$responseObj = new stdClass();
						$responseObj->success = false;
						$responseObj->msg = "Combinaison invalide.";
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}
				}else{
					$responseObj = new stdClass();
					$responseObj->success = false;
					$responseObj->msg = "";
					$responseJSON = json_encode($responseObj);
					echo $responseJSON;
				}
			}else{
				$responseObj = new stdClass();
				$responseObj->success = false;
				$responseObj->msg = "";
				$responseJSON = json_encode($responseObj);
				echo $responseJSON;
			}
		}

		private function creerCompte()
		{
			$auth = new Authentification();

			$body = json_decode(file_get_contents('php://input'));

			// validations back end
			if	(isset($body->courriel) && isset($body->nom) && isset($body->prenom) && isset($body->password)
				&& !empty(trim($body->courriel)) && !empty(trim($body->nom)) && !empty(trim($body->prenom)) && !empty(trim($body->password))){

				// test regex
				$regexCourriel = '/^[\w\-\.]+@([\w\-]+\.)+[\w\-]{2,4}$/i';
				$regexNomPrenom = '/^[\u4e00-\u9fa5a-zà-ÿ \',\-"]{1,}$/i';
				$regexPassword = '/^(?=.*[0-9])(?=.*[a-z])([a-z0-9!@#$%^&*;.,\-_\'"]{4,})$/i';

				if (preg_match($regexCourriel, $body->courriel) && preg_match($regexNomPrenom, $body->nom) && preg_match($regexNomPrenom, $body->prenom) && preg_match($regexPassword, $body->password)){
					$valide = $auth->creerCompte($body->courriel, $body->nom, $body->prenom, $body->password);

					if($valide){
						$responseObj = new stdClass();
						$responseObj->success = true;
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}else{
						$responseObj = new stdClass();
						$responseObj->success = false;
						$responseObj->msg = "Ce courriel existe déjà.";
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}
				}else{
					$responseObj = new stdClass();
					$responseObj->success = false;
					$responseObj->msg = "";
					$responseJSON = json_encode($responseObj);
					echo $responseJSON;
				}
			}else{
				$responseObj = new stdClass();
				$responseObj->success = false;
				$responseObj->msg = "";
				$responseJSON = json_encode($responseObj);
				echo $responseJSON;
			}
		}

		// accueil publique (usager qui n'est pas encore authentifié)
		private function accueil()
		{
			include("vues/welcome.php");     
		}

		// cette méthode se nommait "accueil" avant
		private function accueilUsager()
		{
			if(isset($_SESSION['courriel'])){
				if($_SESSION['courriel'] == "admin_pw2@cmaisonneuve.qc.ca"){
					include("vues/enteteAdmin.php");
					include("vues/mainAdmin.php");
					include("vues/pied.php");  
				}else{
					$bte = new Bouteille();
					$data = $bte->getListeBouteilleCellier();
	
					// pour afficher le nom d'usager
					$usager = new Usager();
					$dataUsager = $usager->getUserByCourriel($_SESSION['courriel']);
	
					include("vues/entete.php");
					include("vues/cellier.php");
					include("vues/pied.php");  
				}
			}else{
				$this->accueil();
			}  
		}

		private function listeBouteille()
		{
			$bte = new Bouteille();
            $cellier = $bte->getListeBouteilleCellier();
            echo json_encode($cellier);  
		}
		
		private function autocompleteBouteille()
		{
			$bte = new Bouteille();
			$body = json_decode(file_get_contents('php://input'));
            $listeBouteille = $bte->autocomplete($body->nom);
            echo json_encode($listeBouteille);    
		}

		private function ajouterNouvelleBouteilleCellier()
		{
			$body = json_decode(file_get_contents('php://input'));

			if(!empty($body)){

				if(isset($body->id_bouteille) && isset($body->date_achat) && isset($body->prix) && isset($body->quantite)
					&& !empty(trim($body->id_bouteille)) && !empty($body->date_achat) && !empty($body->prix) && !empty(trim($body->quantite))){

					// test regex
					$regexPrix = '/^(0|[1-9]\d*)(\.[0-9]{2})$/';
					$regexQuantite = '/^(0|[1-9]\d*)$/';
					$regexDateAchat = '/^[1-2][0-9]{3}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/';
					// TODO : RAJOUTER LES VALIDATIONS POUR LES CHAMPS NON OBLIGATOIRES
					// $regexNoteGarde = '/^[0-9a-zà-ÿ\'",\.\-;!)(?@#$%^&:*+_ ]{0,200}$/';
					
					if(preg_match($regexPrix, $body->prix) && preg_match($regexQuantite, $body->quantite) && preg_match($regexDateAchat, $body->date_achat)){

						$bte = new Bouteille();

						$resultat = $bte->ajouterBouteilleCellier($body);

						if($resultat){
							$responseObj = new stdClass();
							$responseObj->success = true;
							$responseJSON = json_encode($responseObj);
							echo $responseJSON;
						}else{
							$responseObj = new stdClass();
							$responseObj->success = false;
							$responseObj->msg = "Impossible d'ajouter cette bouteille.";
							$responseJSON = json_encode($responseObj);
							echo $responseJSON;
						}
					}else{
						$responseObj = new stdClass();
						$responseObj->success = false;
						$responseObj->msg = "Un ou plusieurs champs invalides.";
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}
				}else{
					$responseObj = new stdClass();
					$responseObj->success = false;
					$responseObj->msg = "Veuillez remplir les champs obligatoires (nom, date d'achat, prix et quantité).";
					$responseJSON = json_encode($responseObj);
					echo $responseJSON;
					
				}

				
			}else{
				// echo json_encode($resultat);
				include("vues/entete.php");
				include("vues/ajouter.php");
				include("vues/pied.php");
			}
		}
		
		private function boireBouteilleCellier()
		{
			$body = json_decode(file_get_contents('php://input'));
			
			$bte = new Bouteille();
			$resultat = $bte->modifierQuantiteBouteilleCellier($body->id, -1);
			echo json_encode($resultat);
		}

		private function ajouterBouteilleCellier()
		{
			$body = json_decode(file_get_contents('php://input'));
			
			$bte = new Bouteille();
			$resultat = $bte->modifierQuantiteBouteilleCellier($body->id, 1);
			echo json_encode($resultat);
		}

		 private function formModificationBtl()
		 {
			$bte = new Bouteille();
			$data = $bte->getListeBouteilleCellierById($_GET['id']);
			
			include("vues/entete.php");
			include("vues/modificationBtl.php");
			include("vues/pied.php");
		 }

		private function sauvegardeBouteille()
		{
			$requestPayload = file_get_contents('php://input');
			$object = json_decode($requestPayload, true);
			//var_dump($object);

			if(isset($object['nomBtl']) && isset($object['date_achat']) && isset($object['prix']) && isset($object['quantite'])
				&& !empty($object['nomBtl']) && !empty($object['date_achat']) && !empty($object['prix']) && !empty(trim($object['quantite']))){

				// test regex
				$regexPrix = '/^(0|[1-9]\d*)(\.[0-9]{2})$/';
				$regexQuantite = '/^(0|[1-9]\d*)$/';
				$regexDateAchat = '/^[1-2][0-9]{3}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/';
				// TODO : RAJOUTER LES VALIDATIONS POUR LES CHAMPS NON OBLIGATOIRES
				// $regexNoteGarde = '/^[0-9a-zà-ÿ\'",\.\-;!)(?@#$%^&:*+_ ]{0,200}$/';

				if(preg_match($regexPrix, $object['prix']) && preg_match($regexQuantite, $object['quantite']) && preg_match($regexDateAchat, $object['date_achat'])){

					$bte = new Bouteille();
					$resultat = $bte->modificationInfoBtl($object['nomBtl'],$object['btlIdPK'],$object['date_achat'],$object['garde'],$object['notes'],$object['prix'],$object['quantite'],$object['millesime']);
					
					if($resultat){
						$responseObj = new stdClass();
						$responseObj->success = true;
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}else{
						$responseObj = new stdClass();
						$responseObj->success = false;
						$responseObj->msg = "Impossible de modifier cette bouteille.";
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}
				}else{
					$responseObj = new stdClass();
					$responseObj->success = false;
					$responseObj->msg = "Un ou plusieurs champs invalides.";
					$responseJSON = json_encode($responseObj);
					echo $responseJSON;
				}
			}else{
				$responseObj = new stdClass();
				$responseObj->success = false;
				$responseObj->msg = "Veuillez remplir les champs obligatoires (nom, date d'achat, prix et quantité).";
				$responseJSON = json_encode($responseObj);
				echo $responseJSON;
			}
		}

		private function supprimerBouteilleCellier(){

			$body = json_decode(file_get_contents('php://input'));

			if(isset($_SESSION['courriel']) && isset($body->id)){

				$bte = new Bouteille();

				$resultat = $bte->supprimerBouteilleCellier($body);

				if($resultat){
					$responseObj = new stdClass();
					$responseObj->success = true;
					$responseJSON = json_encode($responseObj);
					echo $responseJSON;
				}else{
					$responseObj = new stdClass();
					$responseObj->success = false;
					$responseObj->msg = "Impossible de supprimer la bouteille.";
					$responseJSON = json_encode($responseObj);
					echo $responseJSON;
				}
			}else{
				$responseObj = new stdClass();
				$responseObj->success = false;
				$responseObj->msg = "Paramètres manquants.";
				$responseJSON = json_encode($responseObj);
				echo $responseJSON;
			}

		}


		private function modifierCompte()
		{
			$usager = new Usager();
			$data = $usager->getUserByCourriel($_SESSION['courriel']);
			
			include("vues/entete.php");
			include("vues/compte.php");
			include("vues/pied.php");

		}


		private function sauvegardeCompte()
		{

			$body = json_decode(file_get_contents('php://input'));

			if(isset($_SESSION['courriel']) && isset($body->nom) && isset($body->prenom) && !empty($body->nom) && !empty($body->prenom)){

				// test regex
				$regexNomPrenom = '/^[\u4e00-\u9fa5a-zà-ÿ \',\-"]{1,}$/i';
				$regexPassword = '/^(?=.*[0-9])(?=.*[a-z])([a-z0-9!@#$%^&*;.,\-_\'"]{4,})$/i';

				// cas par defaut si on a pas change le password
				$passwordValide = true;

				// le seul cas où la validation du password serait incorecte
				if(isset($body->mot_de_passe) && !empty($body->mot_de_passe) && !preg_match($regexPassword, $body->mot_de_passe)){
					$passwordValide = false;
				}
				
				if(preg_match($regexNomPrenom, $body->nom) && preg_match($regexNomPrenom, $body->prenom) && $passwordValide){

					$usager = new Usager();
					$resultat = $usager->sauvegardeModificationCompte($body->nom,$body->prenom, $body->mot_de_passe); 

					if($resultat){
						$responseObj = new stdClass();
						$responseObj->success = true;
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}else{
						$responseObj = new stdClass();
						$responseObj->success = false;
						$responseObj->msg = "Impossible de modifier les informations.";
						$responseJSON = json_encode($responseObj);
						echo $responseJSON;
					}
				}else{
					$responseObj = new stdClass();
					$responseObj->success = false;
					$responseObj->msg = "Paramètres Invalides.";
					$responseJSON = json_encode($responseObj);
					echo $responseJSON;
				}
			}else{
				$responseObj = new stdClass();
				$responseObj->success = false;
				$responseObj->msg = "Paramètres manquants.";
				$responseJSON = json_encode($responseObj);
				echo $responseJSON;
			}
		}

		// private function sauvegardeCompte()
		// {
		// 	/**
		// 	 * *******************
		// 	 * To Do
		// 	 * récupérer id d'usager quand authentification
		// 	 * *******************
		// 	 */
		// 	$usager = new Usager();
		// 	$usager->sauvegardeModificationCompte($_POST['userId'], $_POST['nom'],$_POST['prenom'], $_POST['mot_de_passe']); 

		// 	$bte = new Bouteille();
		// 	$data = $bte->getListeBouteilleCellier();
		// 	include("vues/entete.php");
		// 	include("vues/cellier.php");
		// 	include("vues/pied.php"); 
		// }
		
		private function getCurrentUser()
		{

		}

		private function deconnexion(){

			//fermer la session usager
			session_destroy();

			$this->accueil();
		}
}
?>















