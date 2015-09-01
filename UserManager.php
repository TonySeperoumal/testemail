<?php

namespace Manager;

/**
 * Le manager de la table users
 */
class UserManager extends \W\Manager\UserManager
{
	/**
	 * Ajoute une ligne
	 * @param array $data Un tableau associatif de valeurs à insérer
	 * @param boolean $stripTags Active le strip_tags automatique sur toutes les valeurs
	 * @return mixed La valeur de retour de la méthode execute()
	 */
	public function insert(array $data, $stripTags = true)
	{

		$colNames = array_keys($data);
		$colNamesString = implode(", ", $colNames);

		$sql = "INSERT INTO " . $this->table . " ($colNamesString) VALUES (";
		foreach($data as $key => $value){
			$sql .= ":$key, ";
		}
		$sql = substr($sql, 0, -2);
		$sql .= ")";

		$sth = $this->dbh->prepare($sql);
		foreach($data as $key => $value){
			$value = ($stripTags) ? strip_tags($value) : $value;
			$sth->bindValue(":".$key, $value);
		}
		$sth->execute();

		return $this->dbh->lastInsertId();
	}

	public function getToken($token)
	{
		
		$sql = "SELECT * FROM users
				WHERE token = :token";
		$sth = $this->dbh->prepare($sql);
		$sth->bindValue(":token", $token);
		$sth->execute();

		$user = $sth->fetch();
		return $user;
	}

	
}