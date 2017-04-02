<?php

namespace App\Model;

use App\Enum\UserRoleEnum;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\DogOwnerEntity;
use Dibi\Exception;
use Nette;
use App\Model\Entity\UserEntity;
use Nette\InvalidStateException;
use Nette\Security\Passwords;

class UserRepository extends BaseRepository implements Nette\Security\IAuthenticator {

	const PASSWORD_COLUMN = 'password';

	/** string znak pro nevybran�ho veterin�� v selectu  */
	const NOT_SELECTED = "-";

	/**
	 * Performs an authentication.
	 *
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) {
		$email = (isset($credentials['email']) ? $credentials['email'] : "");
		$password = (isset($credentials['password']) ? $credentials['password'] : "");

		$query = ["select * from user where email = %s", $email, " and active = 1"];
		$row = $this->connection->query($query)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		} elseif (!Passwords::verify($password, $row[self::PASSWORD_COLUMN])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		$userEntity = new UserEntity();
		$userEntity->hydrate($row->toArray());

		$arr = $row->toArray();
		unset($arr[self::PASSWORD_COLUMN]);

		return new Nette\Security\Identity($userEntity->getId(), $userEntity->getRole(), $arr);
	}

	/**
	 * @return UserEntity[]
	 */
	public function findUsers() {
		$query = "select * from user";
		$result = $this->connection->query($query);

		$users = [];
		foreach ($result->fetchAll() as $row) {
			$user = new UserEntity();
			$user->hydrate($row->toArray());
			$users[] = $user;
		}

		return $users;
	}

	/**
	 * @param int $id
	 * @return UserEntity
	 */
	public function getUser($id) {
		$query = ["select * from user where id = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$userEntity = new UserEntity();
			$userEntity->hydrate($row->toArray());
			return $userEntity;
		}
	}

	/**
	 * @param int $id
	 * @return UserEntity
	 */
	public function getUserByEmail($email) {
		$query = ["select * from user where email = %s", $email];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$userEntity = new UserEntity();
			$userEntity->hydrate($row->toArray());
			return $userEntity;
		}
	}

	/**
	 * @param int $id
	 * @return UserEntity
	 */
	public function resetUserPassword(UserEntity $userEntity) {
		$input = 'abcdefghijklmnopqrstuvwxyz123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$password = '';
		for ($i = 0; $i < 8; $i++) {
			$password .= $input[mt_rand(0, 60)];
		}

		$query = ["update user set password = %s where email = %s", Passwords::hash($password), $userEntity->getEmail()];
		$this->connection->query($query);

		return $password;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function deleteUser($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from user where id = %i", $id ];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}

	/**
	 * @param UserEntity $userEntity
	 */
	public function saveUser(UserEntity $userEntity) {
		if ($userEntity->getId() == null) {
			$userEntity->setLastLogin('0000-00-00 00:00:00');
			$userEntity->setRegisterTimestamp(date('Y-m-d H:i:s'));
			$query = ["insert into user ", $userEntity->extract()];
		} else {
			$updateArray = $userEntity->extract();
			unset($updateArray['id']);
			unset($updateArray['register_timestamp']);
			unset($updateArray['last_login']);
			$query = ["update user set ", $updateArray, "where id=%i", $userEntity->getId()];
		}

		$this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @param string $newPassString
	 * @return \Dibi\Result|int
	 */
	public function changePassword($id, $newPassString) {
		$newPassHashed = Passwords::hash($newPassString);
		$query = ["update user set password = %s where id = %i", $newPassHashed, $id];
		return $this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function setUserActive($id) {
		$query = ["update user set active = 1 where id = %i", $id];
		return $this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function setUserInactive($id) {
		$query = ["update user set active = 0 where id = %i", $id];
		return $this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function updateLostLogin($id) {
		$query = ["update user set last_login = NOW() where id = %i", $id];
		return $this->connection->query($query);
	}

	/**
	 * @return array
	 */
	public function findBreedersForSelect() {
		$breeders[0] = self::NOT_SELECTED;
		$query = ["select * from user where role = %i", UserRoleEnum::USER_BREEDER];
		$result = $this->connection->query($query);

		foreach ($result->fetchAll() as $row) {
			$user = new UserEntity();
			$user->hydrate($row->toArray());
			$breeders[$user->getId()] = $user->getTitleBefore() . " " . $user->getName() . " " . $user->getSurname() . " ". $user->getTitleAfter();
		}

		return $breeders;
	}

	/**
	 * @param int $pID
	 * @return BreederEntity
	 */
	public function getBreederByDog($pID) {
		$query = ["select * from appdata_chovatel where pID = %i", $pID];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$breederEntity = new BreederEntity();
			$breederEntity->hydrate($row->toArray());
			return $breederEntity;
		}
	}

	/**
	 * @param int $pID
	 * @return array
	 */
	public function findDogOwners($pID) {
		$owners = [];
		$query = ["select * from appdata_majitel where pID = %i and Soucasny = %i", $pID, 1];
		$result = $this->connection->query($query);

		foreach ($result->fetchAll() as $row) {
			$dogOwnerEntity = new DogOwnerEntity();
			$dogOwnerEntity->hydrate($row->toArray());
			$owners[] = $dogOwnerEntity->getUID();
		}

		return $owners;
	}

	/**
	 * @param int $pID
	 * @return array
	 */
	public function findDogPreviousOwners($pID) {
		$owners = [];
		$query = ["select * from appdata_majitel as am left join user as u on am.uID = u.id where am.pID = %i and am.Soucasny = %i", $pID, 0];
		$result = $this->connection->query($query);

		foreach ($result->fetchAll() as $row) {
			$user = new UserEntity();
			$user->hydrate($row->toArray());
			$owners[] = $user;
		}

		return $owners;
	}

	/**
	 * @return array
	 */
	public function findOwnersForSelect() {
		$owners = [];
		$query = ["select * from user"]; //where, UserRoleEnum::USER_OWNER];
		$result = $this->connection->query($query);

		foreach ($result->fetchAll() as $row) {
			$user = new UserEntity();
			$user->hydrate($row->toArray());
			$name = trim($user->getTitleBefore()) . " " . trim($user->getName()) . " " . trim($user->getSurname()) . " ". trim($user->getTitleAfter());
			$name = (strlen($name) > 60 ? substr($name, 0, 60)."..." : $name);
			$owners[$user->getId()] = $name;
		}

		return $owners;
	}
}