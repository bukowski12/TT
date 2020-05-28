<?php

use Nette\Security,
	Nette\Utils\Strings;


/**
 * Users authenticator.
 */
class Authenticator extends Nette\Object implements Security\IAuthenticator
{
	/** @var Nette\Database\Connection */
	private $database;


	public function __construct(Nette\Database\Connection $database)
	{
		$this->database = $database;
	}


	/**
	 * Performs an authentication.
	 * @param  array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->database->table('user')->where('login', $username)->fetch();

		if (!$row) {
			throw new Security\AuthenticationException('Login neexistuje.', self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $this->generateHash($password, $row->password)) {
			throw new Security\AuthenticationException('Špatné heslo.', self::INVALID_CREDENTIAL);
		}

		unset($row->password);
		return new Security\Identity($row->idUser, NULL, $row->toArray());
	}


	/**
	 * Computes salted password hash.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function generateHash($password, $salt = NULL)
	{
		if ($password === Strings::upper($password)) { // perhaps caps lock is on
			$password = Strings::lower($password);
		}
		return crypt($password, $salt ?: '$2x$07$' . Strings::random(22));
	}


	public function changePass($login, $old, $new ,$confirm)
	{
		$row = $this->database->table('user')->where('login', $login)->fetch();
		
		if (!$row) {
			throw new Security\AuthenticationException('Nejste přihlášen.', self::IDENTITY_NOT_FOUND);
		}

		if ($row->password !== $this->generateHash($old, $row->password)) {
			throw new Security\AuthenticationException('Špatné heslo.', self::INVALID_CREDENTIAL);
		}

		$this->database->table('user')->where(array('login' => $login))->update(array('password' => $this->generateHash($new)));
	}

}
