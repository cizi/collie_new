<?php

namespace App\Model;

use App\Enum\DogStateEnum;
use App\Enum\LitterApplicationStateEnum;
use App\Forms\DogFilterForm;
use App\Model\Entity\BreederEntity;
use App\Model\Entity\BreederTemporary;
use App\Model\Entity\DogEntity;
use App\Model\Entity\DogFileEntity;
use App\Model\Entity\DogHealthEntity;
use App\Model\Entity\DogOwnerEntity;
use App\Model\Entity\DogPicEntity;
use App\Model\Entity\LitterApplicationEntity;
use App\Model\Entity\UserTemporaryEntity;
use Dibi\Connection;
use Dibi\Row;
use Nette\Application\UI\Presenter;
use Nette\Http\Session;
use Nette\Utils\DateTime;
use Nette\Utils\Paginator;

class DogRepository extends BaseRepository {

	/** @const klíč pro poslední předcůdce psa */
	const SESSION_LAST_PREDECESSOR = 'lastPredecessor';

	/** @const znak pro nevybraného psa v selectu  */
	const NOT_SELECTED = "-";

	/** @const pořadí pro fenu */
	const FEMALE_ORDER = 30;

	/** @const pořadí pro psa */
	const MALE_ORDER = 29;

	/** @const pořadí pro jednotlivá zdraví  */
	const DKK_ORDER = 65;
	const DLK_ORDER = 66;
	const DOV_ORDER = 69;
	const MDR_ORDER = 62;

	/** @var EnumerationRepository  */
	private $enumRepository;

	/** @var \Dibi\Connection */
	protected $connection;

	/** @var Session */
	private $session;

	/** @var LangRepository */
	private $langRepository;

	/** @var LitterApplicationRepository */
	private $litterApplicationRepository;

	/** @var TemporaryUserRepository */
	private $temporaryUserRepository;

	/** @var array pole výyskytu psů během vypisování rodokmenu */
	private $avkTable = [];

	/** @var array pole barev podle výskytu */
	private $colorByDogId = [];

	/** @var HealthOrderRepository */
	private $healthOrderRepository;

	/** @var array */
	private $healthOrder;

	/** @var array pole možných barev pokud je deepmark */
	private $backgroundColors = [
		"#CC66FF", "#CC99FF", "#3399FF", "#66CCFF", "#FFCC66", "#996699", "#CC66CC", "#CC6666", "#CC9900", "#FF9999", "#999900", "#00CCCC",
		"#66CCCC", "#FFCC00", "#CCCC66", "#66CCCC", "#FFCCFF", "#99CC33", "#66FFFF", "#666666", "#999999", "#FF9933", "#999933", "#669999",

		"#F0F8FF", "#E32636", "#E52B50", "#FFBF00", "#8DB600", "#FBCEB1", "#7FFFD4", "#3B444B", "#E9D66B", "#B2BEB5", "#87A96B", "#FF9966", "#89CFF0",
		"#A1CAF1", "#F4C2C2", "#FFD12A", "#F5F5DC", "#3D2B1F", "#FAF0BE", "#DE5D83", "#79443B", "#CC0000", "#B5A642", "#66FF00", "#BF94E4", "#C32148",
		"#FF007F", "#08E8DE", "#D19FE8", "#004225", "#CD7F32", "#964B00", "#FFC1CC", "#E7FEFF", "#F0DC82", "#800020", "#DEB887", "#CC5500", "#E97451",
		"#006B3C", "#ED872D", "#E30022", "#A3C1AD",	"#78866B", "#FFEF00", "#FF0800", "#C41E3A", "#00CC99", "#960018", "#99BADD",  "#ED9121", "#92A1CF",
		"#ACE1AF", "#007BA7", "#2A52BE", "#A0785A", "#F7E7CE", "#36454F", "#DFFF00", "#DE3163", "#FFB7C5", "#CD5C5C", "#7B3F00", "#FFA700", "#98817B",
		"#E34234", "#D2691E", "#E4D00A", "#FBCCE7", "#00FF6F", "#0047AB", "#9BDDFF", "#002E63", "#8C92AC", "#B87333", "#FF3800", "#FF7F50", "#893F45",
		"#FBEC5D", "#B31B1B", "#6495ED", "#FFF8DC", "#FFFDD0", "#DC143C", "#00FFFF", "#FFFF31", "#F0E130", "#00008B", "#654321", "#5D3954", "#A40000",
		"#08457E", "#986960", "#CD5B45", "#008B8B", "#B8860B", "#013220", "#1A2421", "#BDB76B", "#483C32", "#734F96", "#8B008B", "#003366", "#556B2F",
		"#FF8C00", 	"#779ECB", "#03C03C", "#966FD6",  "#C23B22", "#E75480","#003399", "#872657", "#E9967A", "#560319", "#3C1414", "#2F4F4F", "#177245",
		"#918151", "#FFA812", "#CC4E5C", "#9400D3", "#555555", "#1560BD", "#C19A6B", "#EDC9AF", "#696969", "#85BB65", "#00009C", "#E1A95F", "#614051",
		"#8A3324", "#BD33A4", "#702963", "#536878"
	];

	/**
	 * @param EnumerationRepository $enumerationRepository
	 * @param Connection $connection
	 * @param Session $session
	 * @param LangRepository $langRepository
	 */
	public function __construct(
		EnumerationRepository $enumerationRepository,
		Connection $connection,
		Session $session,
		LangRepository $langRepository,
		LitterApplicationRepository $litterApplicationRepository,
		TemporaryUserRepository $temporaryUserRepository,
        HealthOrderRepository $healthOrderRepository
	) {
		$this->enumRepository = $enumerationRepository;
		$this->session = $session;
		$this->langRepository = $langRepository;
		$this->litterApplicationRepository = $litterApplicationRepository;
		$this->temporaryUserRepository = $temporaryUserRepository;
        $this->healthOrderRepository = $healthOrderRepository;

		parent::__construct($connection);

		$this->healthOrder = $this->healthOrderRepository->findOrders();
	}

	/**
	 * @param int $id
	 * @return DogEntity
	 */
	public function getDog($id) {
		$query = ["select * from appdata_pes where ID = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$dogEntity = new DogEntity();
			$dogEntity->hydrate($row->toArray());
			return $dogEntity;
		}
	}

	/**
	 * @param int $id
	 * @return string
	 */
	public function getName($id) {
		$name = "";
		$dog = $this->getDog($id);
		if ($dog != null) {
			$name = trim($dog->getTitulyPredJmenem() . " " . $dog->getJmeno() . " " . $dog->getTitulyZaJmenem());
		}

		return $name;
	}

	/**
	 * @return array
	 */
	public function findFemaleDogsForSelect($withNotSelectedOption = true) {
		$query = ["select `ID`, `Jmeno` from appdata_pes where Stav = %i and Pohlavi = %i", DogStateEnum::ACTIVE, self::FEMALE_ORDER];
		$result = $this->connection->query($query);
		$dogs = [];

		if ($withNotSelectedOption) {
			$dogs[0] = self::NOT_SELECTED;
		}
		foreach ($result->fetchAll() as $row) {
			$dog = $row->toArray();
			$dogs[$dog['ID']] = trim($dog['Jmeno']);
		}

		return $dogs;
	}

	/**
	 * @return array
	 */
	public function findDogsForSelect($withNotSelectedOption = true) {
		$query = ["select `ID`, `Jmeno` from appdata_pes where Stav = %i", DogStateEnum::ACTIVE];
		$result = $this->connection->query($query);
		$dogs = [];

		if ($withNotSelectedOption) {
			$dogs[0] = self::NOT_SELECTED;
		}
		foreach ($result->fetchAll() as $row) {
			$dog = $row->toArray();
			$dogs[$dog['ID']] = trim($dog['Jmeno']);
		}

		return $dogs;
	}

	/**
	 * @return DogEntity[]
	 */
	public function findMaleDogsForSelect($withNotSelectedOption = true) {
		$query = ["select `ID`, `Jmeno` from appdata_pes where Stav = %i and Pohlavi = %i", DogStateEnum::ACTIVE, self::MALE_ORDER];
		$result = $this->connection->query($query);
		$dogs = [];

		if ($withNotSelectedOption) {
			$dogs[0] = self::NOT_SELECTED;
		}
		foreach ($result->fetchAll() as $row) {
			$dog = $row->toArray();
			$dogs[$dog['ID']] = trim($dog['Jmeno']);
		}

		return $dogs;
	}

	/**
	 * @return DogEntity[]
	 */
	public function findDogs(Paginator $paginator, array $filter, $owner = null) {
		if (empty($filter) && ($owner == null)) {
			$query = ["select * from appdata_pes where Stav = %i order by `Jmeno` asc limit %i , %i", DogStateEnum::ACTIVE, $paginator->getOffset(), $paginator->getLength()];
		} else {
			$query[] = "select *, SPLIT_STR(CisloZapisu, '/', 3) as PlemenoCZ, ap.ID as ID from appdata_pes as ap ";
			foreach ($this->getJoinsToArray($filter, $owner) as $join) {
				$query[] = $join;
			}
			$query[] = "where Stav = " . DogStateEnum::ACTIVE . " ";
			$query[] = $this->getWhereFromKeyValueArray($filter, $owner);
			if (isset($filter[DogFilterForm::DOG_FILTER_ORDER_NUMBER])) {
				$query[] = " order by PlemenoCZ " . (($filter[DogFilterForm::DOG_FILTER_ORDER_NUMBER]) == 2 ? "desc" : "asc") . " limit %i , %i";
			} else {
				$query[] = " order by `Jmeno` asc limit %i , %i";
			}
			$query[] = $paginator->getOffset();
			$query[] = $paginator->getLength();
		}
		$result = $this->connection->query($query);

		$dogs = [];
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[] = $dog;
		}

		return $dogs;
	}

	/**
	 * @param array $filter
	 * @return int
	 */
	public function getDogsCount(array $filter, $owner = null) {
		if (empty($filter) && ($owner == null)) {
			$query = "select count(ID) as pocet from appdata_pes where Stav = " . DogStateEnum::ACTIVE;
		} else {
			$query[] = "select count(distinct ap.ID) as pocet from appdata_pes as ap ";
			foreach ($this->getJoinsToArray($filter, $owner) as $join) {
				$query[] = $join;
			}
			$query[] = "where Stav = " . DogStateEnum::ACTIVE . " ";
			$query[] = $this->getWhereFromKeyValueArray($filter, $owner);
		}
		$row = $this->connection->query($query);

		return ($row ? $row->fetch()['pocet'] : 0);
	}

	/**
	 * Připraví joiny tabulek
	 * @param array $filter
	 * @return array
	 */
	private function getJoinsToArray($filter, $owner = null) {
		$joins = [];
		if ($owner != null) {
			$joins[] = "left join `appdata_majitel` as am on ap.ID = am.pID";
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_LAND]) || isset($filter[DogFilterForm::DOG_FILTER_BREEDER])) {
			$joins[] = "left join `appdata_chovatel` as ac on ap.ID = ac.pID
						left join `user` as u on ac.uID = u.ID ";
			unset($filter[DogFilterForm::DOG_FILTER_LAND]);
			unset($filter[DogFilterForm::DOG_FILTER_BREEDER]);
		}

		if (isset($filter[DogFilterForm::DOG_FILTER_OWNER])) {
			$joins[] = "left join `appdata_majitel` as ama on ap.ID = ama.pID
						left join `user` as us on ama.uID = us.ID ";
			unset($filter[DogFilterForm::DOG_FILTER_OWNER]);
		}

		if (
			isset($filter[DogFilterForm::DOG_FILTER_HEALTH])
			|| isset($filter[DogFilterForm::DOG_FILTER_PROB_DKK])
			|| isset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]
			)) {
			$joins[] = "left join `appdata_zdravi` as az on ap.ID = az.pID ";
			unset($filter[DogFilterForm::DOG_FILTER_HEALTH]);
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]);
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]);
		}

		return $joins;
	}

	/**
	 * @param array $filer
	 * @return string
	 */
	private function getWhereFromKeyValueArray(array $filter, $owner = null) {
		// odstraním data, která jsou součástí filteru, ale nepatří do WHERE klauzule
		unset($filter[DogFilterForm::DOG_FILTER_ORDER_NUMBER]);	// tohle sem v podstatě nepatří, ale je to souččástí filtru
		$return = ((count($filter) > 0) || ($owner != null) ? " and " : "");

		$dbDriver = $this->connection->getDriver();
		$currentLang = $this->langRepository->getCurrentLang($this->session);
		if ($owner != null) {
			$return .= sprintf("am.uID = %d and am.Soucasny = 1", $owner);	// je to soucasny spravne
			$return .= (count($filter) > 0 ? " and " : "");
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_LAST_14_DAYS])) {
			$return .= " PosledniZmena >= (CURDATE() - INTERVAL 14 DAY)";
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_LAST_14_DAYS]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_LAND])) {
			$return .= sprintf("u.state = %s", $dbDriver->escapeText($filter[DogFilterForm::DOG_FILTER_LAND]));
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_LAND]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_BREEDER])) {
			$return .= sprintf("ac.uID = %d", $filter[DogFilterForm::DOG_FILTER_BREEDER]);
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_BREEDER]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_OWNER])) {
			$return .= sprintf("ama.uID = %d", $filter[DogFilterForm::DOG_FILTER_OWNER]);
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_OWNER]);
		}
		if (isset($filter["Jmeno"])) {
			$return .= 	"(CONCAT_WS(' ', TitulyPredJmenem, Jmeno, TitulyZaJmenem) like \"%".$filter["Jmeno"]."%\")";
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter["Jmeno"]);
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_HEALTH])) {	// pokud mám zdraví omezím výběr
			$return .= sprintf("az.Typ = %d", $filter[DogFilterForm::DOG_FILTER_HEALTH]);
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_HEALTH]);

			if (isset($filter[DogFilterForm::DOG_FILTER_HEALTH_TEXT])) { // ale musím připojit vysledek
				$return .= sprintf("az.Vysledek like %s", $dbDriver->escapeLike($filter[DogFilterForm::DOG_FILTER_HEALTH_TEXT], 1));	// $dbDriver->escapeText($filter[DogFilterForm::DOG_FILTER_HEALTH_TEXT])
				$return .= (count($filter) > 1 ? " and " : "");
			}
		}
		unset($filter[DogFilterForm::DOG_FILTER_HEALTH_TEXT]);	// tohle musím pro unsetnout vždy, protože když to někdo vyplní a nevybere typ zdravi tak je to bezpředmětný

		if (isset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]) || isset($filter[DogFilterForm::DOG_FILTER_PROB_DLK])) {
			$dkk = (isset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]) ? $this->enumRepository->findEnumItemByOrder($currentLang, $filter[DogFilterForm::DOG_FILTER_PROB_DKK]) : "");
			$dlk = (isset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]) ? $this->enumRepository->findEnumItemByOrder($currentLang, $filter[DogFilterForm::DOG_FILTER_PROB_DLK]) : "");
			if ($dkk != "" && $dlk != "") {
				$return .= sprintf("(az.Typ  = " . DogRepository::DKK_ORDER . " and az.Vysledek like %s) and (az.Typ = " . DogRepository::DLK_ORDER . " and az.Vysledek like %s)", $dbDriver->escapeLike($dkk, 1), $dbDriver->escapeLike($dlk, 1));
				//$return .= "((az.Typ = 65 and az.Vysledek = '"  . $dkk . "') and (az.Typ = 66 and az.Vysledek = '"  . $dlk . "'))";
			} else if ($dkk != "") {
				$return .= sprintf("(az.Typ = " . DogRepository::DKK_ORDER . " and az.Vysledek like %s)", $dbDriver->escapeLike($dkk, 1));
			} else if ($dlk != "") {
				$return .= sprintf("(az.Typ = " . DogRepository::DLK_ORDER . " and az.Vysledek like %s)", $dbDriver->escapeLike($dlk, 1));
			}
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DKK]);
			unset($filter[DogFilterForm::DOG_FILTER_PROB_DLK]);
			$return .= (count($filter) > 0 ? " and " : "");
		}
		if (isset($filter[DogFilterForm::DOG_FILTER_BIRTDATE])) {	// datum narození
			if (strpos($filter[DogFilterForm::DOG_FILTER_BIRTDATE], 'from') !== false) {
				$return .= sprintf(" YEAR(DatNarozeni) >= %s", $dbDriver->escapeText(str_replace("from", "", $filter[DogFilterForm::DOG_FILTER_BIRTDATE])));
			} else {
				$return .= sprintf(" YEAR(DatNarozeni) = %s", $dbDriver->escapeText($filter[DogFilterForm::DOG_FILTER_BIRTDATE]));
			}
			$return .= (count($filter) > 1 ? " and " : "");
			unset($filter[DogFilterForm::DOG_FILTER_BIRTDATE]);
		}

		$i = 0;
		foreach ($filter as $key => $value) {
			if ($key == DogFilterForm::DOG_FILTER_EXAM) {	// like
				$return .= 	sprintf("`Zkousky` like %s", $dbDriver->escapeLike($value, 0));
			} else {	// where
				$return .= sprintf("%s = %s", $key, $dbDriver->escapeText($value));
			}
			if (($i+1) != count($filter)) {
				$return .= " and ";
			}
			$i++;
		}

		return $return;
	}

	/**
	 * @param bool|true $withNotSelectedOption
	 * @return array
	 */
	public function findBirtYearsForSelect($withNotSelectedOption = true) {
		$years = [];
		if ($withNotSelectedOption) {
			 $years[0] = self::NOT_SELECTED;
		}
		$query = ["select DISTINCT YEAR(DatNarozeni) as DatNarozeni from appdata_pes where Stav = %i ORDER BY DatNarozeni DESC", DogStateEnum::ACTIVE];
		$result = $this->connection->query($query);
		foreach ($result->fetchAll() as $row) {
			if ($row['DatNarozeni'] != "") {
				$years[$row['DatNarozeni']] = $row['DatNarozeni'];
				$years['from' . $row['DatNarozeni']] = DOG_TABLE_DOG_YEAR_FROM . " " . $row['DatNarozeni'];
			}
		}

		return $years;
	}

	/**
	 * @param int $pID
	 */
	public function findSiblings($pID) {
		$siblings = [];
		$dog= $this->getDog($pID);
		if (($dog != null) && ($dog->getMID() != null) && ($dog->getOID() != null)) {
			$query = ["select * from appdata_pes where Stav = %i and mID = %i and oID = %i and ID <> %i", DogStateEnum::ACTIVE, $dog->getMID(), $dog->getOID(), $dog->getID()];
			$result = $this->connection->query($query);

			foreach ($result->fetchAll() as $row) {
				$sibling = new DogEntity();
				$sibling->hydrate($row->toArray());
				$siblings[] = $sibling;
			}
		}

		return $siblings;
	}

	/**
	 * @param int $pID
	 * @return DogEntity[]
	 */
	public function findDescendants($pID) {
		$descendants = [];
		$dog = $this->getDog($pID);
		if ($dog != null) {
			if ($dog->getPohlavi() == self::MALE_ORDER) {
				$query = ["select * from appdata_pes where Stav = %i and oID = %i order by (`DatNarozeni` = '0000-00-00'), `DatNarozeni`", DogStateEnum::ACTIVE, $dog->getID()];
			} else {
				$query = ["select * from appdata_pes where Stav = %i and mID = %i order by (`DatNarozeni` = '0000-00-00'), `DatNarozeni`", DogStateEnum::ACTIVE, $dog->getID()];
			}
			$result = $this->connection->query($query);
			foreach ($result->fetchAll() as $row) {
				$descendant = new DogEntity();
				$descendant->hydrate($row->toArray());
				$descendants[] = $descendant;
			}
		}

		return $descendants;
	}

	/**
	 * @return int[]
	 */
	public function findDogsWithDescendants() {
		$query = "select distinct oID from appdata_pes";
		$males = $this->connection->query($query)->fetchPairs("oID","oID");

		$query = "select distinct mID from appdata_pes";
		$females = $this->connection->query($query)->fetchPairs("mID","mID");

		return ($males + $females);
	}

	/**
	 * Uloží/aktualizuje zázanm do tabulky zdraví psa
	 * @param DogHealthEntity $dogHealthEntity
	 */
	public function saveDogHealth(DogHealthEntity $dogHealthEntity) {
		if ($dogHealthEntity->getID() == null) {
			$query = ["insert into appdata_zdravi ", $dogHealthEntity->extract()];
		} else {
			$query = ["update appdata_zdravi set ", $dogHealthEntity->extract(), "where ID=%i", $dogHealthEntity->getID()];
		}
		$this->connection->query($query);
	}

	/**
	 * @param BreederEntity $breederEntity
	 */
	public function saveBreeder(BreederEntity $breederEntity) {
		if ($breederEntity->getUID() == 0) {		// pokud je v selectu vybrána nula tak mažu
			$this->deleteBreederByDogId($breederEntity->getPID());
		} else {
			if ($breederEntity->getID() == null ) {
				$query = ["insert into appdata_chovatel ", $breederEntity->extract()];
			} else {
				$query = ["update appdata_chovatel set ", $breederEntity->extract(), "where ID=%i", $breederEntity->getID()];
			}
			$this->connection->query($query);
		}
	}

	/**
	 * @param DogOwnerEntity $dogOwnerEntity
	 */
	public function saveOwner(DogOwnerEntity $dogOwnerEntity) {
		$alreadyIn = ["select * from appdata_majitel where uID = %i and pID = %i", $dogOwnerEntity->getUID(), $dogOwnerEntity->getPID()];
		$row = $this->connection->query($alreadyIn)->fetch();
		if ($row) {	// pokud existuje akorat přepnu na současného
			$dogOwn = new DogOwnerEntity();
			$dogOwn->hydrate($row->toArray());
			$query = ["update appdata_majitel set Soucasny = %i where ID = %i", $dogOwnerEntity->isSoucasny(), $dogOwn->getID()];
		} else {	// pokud záznam neexistuje vložím jako nový současný majitel
			$query = ["insert into appdata_majitel ", $dogOwnerEntity->extract()];
		}
		$this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return DogHealthEntity[]
	 */
	public function findAllHealthsByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i", $id];
		$result = $this->connection->query($query);

		$dogHealths = [];
		foreach ($result->fetchAll() as $row) {
			$dogHealth = new DogHealthEntity();
			$dogHealth->hydrate($row->toArray());
			$dogHealths[] = $dogHealth;
		}

		return $dogHealths;
	}

	/**
	 * @param int $id
	 * @return DogHealthEntity[]
	 */
	public function findHealthsByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i and Vysledek <> ''", $id];
		$result = $this->connection->query($query);

		$dogHealths = [];
		foreach ($result->fetchAll() as $row) {
			$dogHealth = new DogHealthEntity();
			$dogHealth->hydrate($row->toArray());
			if (isset($this->healthOrder[$dogHealth->getTyp()])) {
                $dogHealths[$this->healthOrder[$dogHealth->getTyp()]] = $dogHealth;
            } else {
			    $dogHealths[] = $dogHealth;
            }
		}
		ksort($dogHealths);

		return $dogHealths;
	}

	/**
	 * Přepnutí psa do režimu smazání
	 * @param int $id
	 * @return bool
	 */
	public function delete($id) {
		$return = true;
		if (!empty($id)) {
			try {
				$query = ["update appdata_pes set `Stav` = %i where ID = %i", DogStateEnum::DELETED, $id];    // pak nastavím psa jako smazaného
				$this->connection->query($query);
			} catch (\Exception $e) {
				$return = false;
			}
		}

		return $return;
	}
	/**
	 * Opravdové smazaní psa z DB pokud to cizí klíče dovolí (jakože spíš ne)
	 * @param int $id
	 * @return bool
	 */
	public function realDelete($id) {
		$return = true;
		if (!empty($id)) {
			try {
				$this->connection->begin();

				$query = ["delete from appdata_pes_obrazky where pID = %i", $id];    // nejdříve smažu obrázky
				$this->connection->query($query);

				$query = ["delete from appdata_pes_soubory where pID = %i", $id];    // pak smažu ostaní soubory
				$this->connection->query($query);

				$this->deleteHealthByDogId($id);
				$this->deleteBreederByDogId($id);
				$this->deleteOwnerByDogId($id);

				$query = ["delete from appdata_pes where ID = %i", $id];    // pak smažu psa
				$this->connection->query($query);

				$this->connection->commit();
			} catch (\Exception $e) {
				$this->connection->rollback();
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * @param int $pID
	 */
	private function deleteHealthByDogId($pID) {
		$query = ["delete from appdata_zdravi where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $pID
	 */
	private function deleteBreederByDogId($pID) {
		$query = ["delete from appdata_chovatel where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $pID
	 */
	private function deleteOwnerByDogId($pID) {
		$query = ["delete from appdata_majitel where pID = %i", $pID];
		$this->connection->query($query);
	}

	/**
	 * @param int $id id záznamu v tabulce
	 */
	public function deleteOwnerById($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from appdata_majitel where ID = %i", $id];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}

	/**
	 * @param int $typ
	 * @param int $pID
	 * @return DogHealthEntity
	 */
	public function getHealthEntityByDogAndType($typ, $pID) {
		$query = ["select * from appdata_zdravi where Typ = %i and pID = %i", $typ, $pID];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$healthEntity = new DogHealthEntity();
			$healthEntity->hydrate($row->toArray());
			return $healthEntity;
		}
	}

	/***
	 * @param int $typ
	 * @param int $pID
	 * @param string $delimiter
	 * @return string
	 */
	public function getHealthByTypeAsStringWithDesc($pID,$typ, $delimiter = ": ") {
		$result = "";
		$query = ["select az.Vysledek, ei.item as Popis from enum_item as ei left join appdata_zdravi as az on ei.order = az.Typ
				where az.Vysledek <> '' and az.pID = %i and az.Typ = %i", $pID, $typ];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$result = $row['Popis'] . $delimiter . $row['Vysledek'];
		}

		return $result;
	}

	/**
	 * @param DogEntity $dogEntity
	 * @param DogPicEntity[]
	 * @param DogHealthEntity[]
	 * @param BreederEntity[]
	 * @param DogOwnerEntity[]
	 * @param DogFileEntity[]
	 * @param int [$mIdOrOidForNewDog]
	 * @param string [$tempBreeder]
	 * @param string [$tempOwner]
	 */
	public function save(
			DogEntity $dogEntity,
			array $dogPics,
			array $dogHealth,
			array $breeders,
			array $owners,
			array $dogFiles,
			$mIdOrOidForNewDog = null,
			$tempBreeder = null,
			$tempOwner = null
	) {
		try {
			$this->connection->begin();
			$dogEntity->setPosledniZmena(new DateTime());
			if ($dogEntity->getMID() == 0) {
				$dogEntity->setMID(null);
			}
			if ($dogEntity->getOID() == 0) {
				$dogEntity->setOID(null);
			}
			if ($dogEntity->getID() == null) {	// nový pes
				$query = ["insert into appdata_pes ", $dogEntity->extract()];
				$this->connection->query($query);
				$dogEntity->setID($this->connection->getInsertId());
			} else {	// editovaný pes
				$query = ["update appdata_pes set ", $dogEntity->extract(), "where ID=%i", $dogEntity->getID()];
				$this->connection->query($query);
			}
			/** @var DogHealthEntity $dogHealthEntity */
			foreach($dogHealth as $dogHealthEntity) {
				$dogHealthEntity->setPID($dogEntity->getID());
				if ($dogHealthEntity->getVeterinar() == 0) {	// pokud nebyl veterinář vybrán vynuluji jeho záznam
					$dogHealthEntity->setVeterinar(null);
				}
				$this->saveDogHealth($dogHealthEntity);
			}
			/** @var BreederEntity $breeder */
			foreach($breeders as $breeder) {
				$breeder->setPID($dogEntity->getID());
				$this->saveBreeder($breeder);
			}

			$query = ["update appdata_majitel set Soucasny = %i where pID = %i", 0, $dogEntity->getID()];	// nevím co mi nyní přijde takže všechny rovnou udělám jako bývalé majitele
			$this->connection->query($query);
			/** @var DogOwnerEntity $owner */
			foreach($owners as $owner) {
				$owner->setPID($dogEntity->getID());
				$this->saveOwner($owner);
			}

			/** @var DogPicEntity $dogPic */
			foreach ($dogPics as $dogPic) {
				$dogPic->setPID($dogEntity->getID());
				$dogPic->setVychozi(0);
				$this->saveDogPic($dogPic);
			}

			/** @var DogFileEntity $dogFile */
			foreach ($dogFiles as $dogFile) {
				$dogFile->setPID($dogEntity->getID());
				$this->saveDogFile($dogFile);
			}

			if ($mIdOrOidForNewDog != null) {	// aktualizujeme potomka o rodiče - pokud je z čeho
				$descendantDog = $this->getDog($mIdOrOidForNewDog);
				if ($descendantDog != null) {
					if ($dogEntity->getPohlavi() == self::MALE_ORDER) {
						$descendantDog->setOID($dogEntity->getID());
					} else {
						$descendantDog->setMID($dogEntity->getID());
					}
					$query = ["update appdata_pes set ", $descendantDog->extract(), "where ID=%i", $descendantDog->getID()];
					$this->connection->query($query);
				}
			}

			// dočasní chovatelé
			$this->temporaryUserRepository->deleteAllTemporaryBreedersByDog($dogEntity->getID());
			if (!empty($tempBreeder) && (trim($tempBreeder) != "")) {
				$tempUser = $this->temporaryUserRepository->getTemporaryUserByName(trim($tempBreeder));
				if ($tempUser == null) {
					$tempUser = new UserTemporaryEntity();
					$tempUser->setCeleJmeno(trim($tempBreeder));
					$tempUser = $this->temporaryUserRepository->saveTemporaryUser($tempUser);
				}
				$this->temporaryUserRepository->setTempUserAsTempBreeder($tempUser, $dogEntity->getID());
			}

			// dočasní majitelé
			$this->temporaryUserRepository->deleteAllTemporaryOwnersByDog($dogEntity->getID());
			if (!empty($tempOwner) && (trim($tempOwner != ""))) {
				$allTempOwners = explode(",", $tempOwner);
				foreach ($allTempOwners as $tempOwnerToDb) {
					if (trim($tempOwnerToDb) != "") {
						$tempUser = $this->temporaryUserRepository->getTemporaryUserByName(trim($tempOwnerToDb));
						if ($tempUser == null) {
							$tempUser = new UserTemporaryEntity();
							$tempUser->setCeleJmeno(trim($tempOwnerToDb));
							$tempUser = $this->temporaryUserRepository->saveTemporaryUser($tempUser);
						}
						$this->temporaryUserRepository->setTempUserAsTempOwner($tempUser, $dogEntity->getID());
					}
				}
			}

			$this->connection->commit();
		} catch (\Exception $e) {
			// echo $e->getMessage(); die;
			$this->connection->rollback();
			throw $e;
		}
	}

	/**
	 * @param DogEntity[] $dogs
	 * @param DogHealthEntity[] $healths
	 * @param array $breeders
	 * @param array $owners
	 * @param LitterApplicationEntity $litterApplicationEntity
	 * @throws \Exception
	 */
	public function saveDescendants(array $dogs, array $healths, array $breeders, array $owners, LitterApplicationEntity $litterApplicationEntity) {
		try {
			$this->connection->begin();
			for($i = 0; $i < count($dogs); $i++) {
				$this->save($dogs[$i], [], $healths[$i], $breeders, $owners, []);
			}
			$litterApplicationEntity->setZavedeno(LitterApplicationStateEnum::REWRITTEN);
			$this->litterApplicationRepository->save($litterApplicationEntity);

			$this->connection->commit();
		} catch (\Exception $e) {
			$this->connection->rollback();
			throw $e;
		}
	}

	/**
	 * @param DogPicEntity $dogPicEntity
	 */
	public function saveDogPic(DogPicEntity $dogPicEntity) {
		$picQuery = ["insert into appdata_pes_obrazky ", $dogPicEntity->extract()];
		$this->connection->query($picQuery);
	}

	/**
	 * @param DogPicEntity $dogFileEntity
	 */
	public function saveDogFile(DogFileEntity $dogFileEntity) {
		$picQuery = ["insert into appdata_pes_soubory ", $dogFileEntity->extract()];
		$this->connection->query($picQuery);
	}

	/**
	 * @param int $pID
	 * @return DogPicEntity[]
	 */
	public function findDogPics($pID) {
		$query = ["select * from appdata_pes_obrazky where pID = %i order by `vychozi` desc" , $pID];
		$result = $this->connection->query($query);

		$pics = [];
		foreach ($result->fetchAll() as $row) {
			$dogPic = new DogPicEntity();
			$dogPic->hydrate($row->toArray());
			$pics[] = $dogPic;
		}

		return $pics;
	}

	/**
	 * @param int $pID
	 * @return DogFileEntity[]
	 */
	public function findDogFiles($pID) {
		$query = ["select * from appdata_pes_soubory where pID = %i order by id, typ desc", $pID];
		$result = $this->connection->query($query);

		$files = [];
		foreach ($result->fetchAll() as $row) {
			$dogFile = new DogFileEntity();
			$dogFile->hydrate($row->toArray());
			$files[] = $dogFile;
		}

		return $files;
	}

	/**
	 * @param int $dogId
	 * @param int $picId
	 */
	public function setDefaultDogPic($dogId, $picId) {
		$query = ["update appdata_pes_obrazky set vychozi=0 where pID = %i", $dogId];
		$this->connection->query($query);
		$query = ["update appdata_pes_obrazky set vychozi=1 where pID = %i and id = %i", $dogId, $picId];
		$this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteDogPic($id) {
		$return = true;
		if (!empty($id)) {
			$query = ["delete from appdata_pes_obrazky where id = %i", $id];
			$return = $this->connection->query($query) == 1 ? true : false;
		}

		return $return;
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteDogFile($id) {
		$return = true;
		if (!empty($id)) {
			$query = ["delete from appdata_pes_soubory where id = %i", $id];
			$return = $this->connection->query($query) == 1 ? true : false;
		}

		return $return;
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getDkkByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i and Typ = %i", $id, 65];
		$result = $this->connection->query($query);

		$row = $result->fetch();
		if ($row) {
			$dogHealth = new DogHealthEntity();
			$dogHealth->hydrate($row->toArray());
			return $dogHealth;
		}
	}

	/**
	 * @param int $id
	 * @return array
	 */
	public function getDlkByDogId($id) {
		$query = ["select * from appdata_zdravi where pID = %i and Typ = %i", $id, 66];
		$result = $this->connection->query($query);

		$row = $result->fetch();
		if ($row) {
			$dogHealth = new DogHealthEntity();
			$dogHealth->hydrate($row->toArray());
			return $dogHealth;
		}
	}

	/**
	 * Najde psy u kterých je uživatel veden jako chovatel
	 *
	 * @param int $userId
	 * @return DogEntity[]
	 */
	public function findDogsByBreeder($userId) {
		$query = [
			"select *, ac.ID as acID from appdata_chovatel as ac left join appdata_pes as ap on ac.pID = ap.ID where ac.uID = %i and ap.Stav = %i order by ap.DatNarozeni ASC",
			$userId, DogStateEnum::ACTIVE
		];
		// $query = ["select * from appdata_chovatel as ac left join appdata_pes as ap on ac.pID = ap.ID where ac.uID = %i", $userId];
		$result = $this->connection->query($query);

		$dogs = [];
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[] = $dog;
		}

		return $dogs;
	}

	/**
	 * Najde psy u kterých je užovatel veden jako stávající vlastník
	 *
	 * @param int $userId
	 * @return DogEntity[]
	 */
	public function findDogsByCurrentOwner($userId) {
		$query = ["select * from appdata_majitel as am left join appdata_pes as ap on am.pID = ap.ID where am.uID = %i and am.Soucasny = 1", $userId];
		$result = $this->connection->query($query);

		$dogs = [];
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[] = $dog;
		}

		return $dogs;
	}

	/**
	 * Najde psy které měl uživatel evidované jako vlastník
	 *
	 * @param int $userId
	 * @return DogEntity[]
	 */
	public function findDogsByPreviousOwner($userId) {
		$query = ["select * from appdata_majitel as am left join appdata_pes as ap on am.pID = ap.ID where am.uID = %i and am.Soucasny = 0", $userId];
		$result = $this->connection->query($query);

		$dogs = [];
		foreach ($result->fetchAll() as $row) {
			$dog = new DogEntity();
			$dog->hydrate($row->toArray());
			$dogs[] = $dog;
		}

		return $dogs;
	}

	/**
	 * Vrátí psa/fenu podle zanoření
	 * @param int $pID
	 * @param int $level
	 * @param bool $male
	 * @return string
	 */
	private function getDogPedigreeByIdLevel($pID, $lang, Presenter $presenter, $isUserAdmin, $level = 1, $male = true) {
		$dog = $this->getDog($pID);
		$searchedDog = "<br />";
		for($i=1; $i<=$level;$i++) {
			$dog = ($male ? $this->getDog($dog->getOID()) : $this->getDog($dog->getMID()));
			if (($i == $level) && ($dog != null)) {
				$searchedDog = $this->formatDogToPedigreeValidation($dog, $lang, $presenter, $isUserAdmin);
			}

		}

		return $searchedDog;
	}

	/***
	 * Naformátuje infoamce o psovi
	 * @param DogEntity $dog
	 * @param $lang
	 * @param Presenter $presenter
	 * @param $isUserAdmin
	 * @return string
	 */
	private function formatDogToPedigreeValidation(DogEntity $dog, $lang, Presenter $presenter, $isUserAdmin) {
		$link = ($isUserAdmin ? $presenter->link('FeItem1velord2:edit', $dog->getID()) : $presenter->link('FeItem1velord2:view', $dog->getID()));
		$formated = '<b><a href="' . $link . '">'.trim($dog->getTitulyPredJmenem() . " " .  $dog->getJmeno() . " " . $dog->getTitulyZaJmenem()).'</a></b><br />';
		$adds = [];

		$plemeno = $dog->getPlemeno();
		if (!empty($plemeno)) {
			$adds[] = $this->enumRepository->findEnumItemByOrder($lang, $dog->getPlemeno());
		}
		$barva = $dog->getBarva();
		if (!empty($barva)) {
			$adds[] = $this->enumRepository->findEnumItemByOrder($lang, $dog->getBarva());
		}
		$vyska = $dog->getVyska();
		if (!empty($vyska)) {
			$adds[] = $dog->getVyska() . ' cm';
		}

		$query = ["SELECT item as Nazev, Vysledek FROM appdata_zdravi as zdravi LEFT JOIN enum_item as ciselnik
				ON (ciselnik.enum_header_id = 14 AND ciselnik.order = zdravi.Typ) WHERE pID = %i ORDER BY Datum DESC", $dog->getID()];
		$zdravi = $this->connection->query($query)->fetchPairs("Nazev","Vysledek");
		foreach ($zdravi as $zdraviTyp => $zdraviVysledek) {
			if (trim($zdraviVysledek) != "") {
				$adds[] = '' . $zdraviTyp . ': ' . $zdraviVysledek;
			}
		}

		return $formated . implode(', ',$adds);
	}

	// ------ příbuzbost ----
	/**
	 * @param int $pID
	 * @param int $fID
	 * @return float
	 */
	public function genealogRelationship($pID,$fID, $levels) {
		$coef = floor($this->genealogRshipGo($pID,$fID,0, $levels)*10000)/100;
		return $coef;
	}

	/**
	 * Výpočet AVK podle rodokmenu
	 * pozn. tabulka avk je plněna během načítání dat rodokmenu
	 * @return float
	 */
	public function genealogAvk(array $allDogsInPedigree) {
		$deduction = 0;
		$allDogsCount = count($allDogsInPedigree);

		foreach ($this->avkTable as $dogId => $numberOfAppearance) {
			if ($numberOfAppearance > 1) {
				$deduction += ($numberOfAppearance - 1);
			}
		}

		$avkCounting = ($allDogsCount - $deduction)/$allDogsCount;
		if ($avkCounting < 0) {
			$avkCounting = 1 + $avkCounting;
		}
		$avk = round($avkCounting * 100, 2);

		return $avk;
	}

	/**
	 * @param int $ID1
	 * @param int $ID2
	 * @param int $level
	 * @return number
	 */
	public function genealogRshipGo($ID1,$ID2,$level = 0, $levels = 4) {
		global $deepMarkArray;
		$deepMarkArray = [];
		$tree1 = array(array());
		$this->genealogGetRshipPedigree(NULL, $ID1, $level, $levels, $tree1);
		$tree1Toc = array_shift($tree1);

		$tree2 = array(array());
		$this->genealogGetRshipPedigree(NULL, $ID2, $level, $levels, $tree2);
		$tree2Toc = array_shift($tree2);

		$coef = 0;
		foreach ($tree1 as $index1 => $dog1) {
			if (in_array($dog1['ID'], $tree2Toc)) {

				//naslo se!!! Najdeme vyskyty v Tree2 a promazeme
				foreach ($tree2 as $index2 => $dog2) {
					if (($dog2['ID'] == $dog1['ID']) and ($dog1['dID'] != $dog2['dID'])) {
						if (!in_array($dog1['ID'], $deepMarkArray)) {
							$deepMarkArray[] = $dog1['ID'];
						}
						$subcoef = pow(0.5, $dog1['level'] + $dog2['level'] + 1);
						$coef += $subcoef;
					}
				}
			}
		}

		return $coef;
	}

	/**
	 * Funkce pro zjisteni pribuznosti
	 *
	 * @param $dID
	 * @param $ID
	 * @param $level
	 * @param $levels
	 * @param $output
	 * @param array $route
	 */
	private function genealogGetRshipPedigree($dID,$ID,$level,$levels,&$output,$route = array()) {
		if (($level > $levels)) {
			return;
		}
		if (($ID == NULL)) {
			$GLOBALS['lastRship'] = false;
			return;
		}
		$query = ["select pes.ID AS ID, pes.Jmeno AS Jmeno, pes.oID AS oID, pes.mID AS mID FROM appdata_pes as pes WHERE ID= %i LIMIT 1", $ID];
		$fetch = $this->connection->query($query)->fetch();
		if ($fetch == false) {
			$row['Jmeno'] = "";
			$row['oID']= "";
			$row['mID'] = "";
		} else {
			$row = $fetch->toArray();
		}
		$output[0][] = $ID;
		$output[] = array(
			'ID' => $ID,
			'Jmeno' => $row['Jmeno'],
			'dID' => $dID,
			'oID' => $row['oID'],
			'mID' => $row['mID'],
			'level' => $level,
			'route' => $route
		);

		$route[] = $ID;
		//if (isset($row['oID'])) {
			$this->genealogGetRshipPedigree($ID,$row['oID'],$level+1,$levels,$output,$route);
		//}
		//if (isset($row['mID'])) {
			$this->genealogGetRshipPedigree($ID,$row['mID'],$level+1,$levels,$output,$route);
		//}
	}

	/**
	 * @param int $ID
	 * @param int $max
	 * @param string $lang
	 * @param Presenter $presenter
	 * @param bool $isUserAdmin
	 * @return string
	 */
	public function genealogDeepPedigree($ID, $max, $lang, Presenter $presenter, $isUserAdmin, $deepMark = false) {
		$this->clearPedigreeSession();
		global $pedigree;
		$query = ["SELECT pes.ID AS ID, pes.Jmeno AS Jmeno, pes.oID AS oID, pes.mID AS mID FROM appdata_pes as pes
										WHERE pes.ID= %i LIMIT 1", $ID];
		$row = $this->connection->query($query)->fetch()->toArray();
		$pedigree = array();
		$this->genealogDPTrace($row['oID'],1,$max, $lang);
		$this->genealogDPTrace($row['mID'],1,$max, $lang);

		return $this->genealogShowDeepPTable($max, $presenter, $ID, $isUserAdmin, $deepMark);
	}

	/**
	 * @param int $ID
	 * @param int $max
	 * @param string $lang
	 * @param Presenter $presenter
	 * @param bool $isUserAdmin
	 * @return string
	 */
	public function genealogDeepPedigreeV2($ID, $max, $lang, Presenter $presenter, $isUserAdmin, $deepMark = false) {
		$this->clearPedigreeSession();
		global $pedigree;
		$dog = $this->getDog($ID);

		$pedigree = array();
		$this->genealogDPTrace($dog->getOID(),1,$max, $lang);
		$this->genealogDPTrace($dog->getMID(),1,$max, $lang);

		$htmlResult = "<table class='deepPedigreeV2'><tr>
				<td " . ($deepMark && isset($this->colorByDogId[$ID]) ? "style='background-color: ". $this->colorByDogId[$ID] ." ;'" : "") . " >".
					$this->formatDogToPedigreeValidation($dog, $lang, $presenter, $isUserAdmin) ."</td>
				<td>". $this->genealogShowDeepPTable($max, $presenter, $ID, $isUserAdmin, $deepMark) ."</td>
			</tr></table>";

		return $htmlResult;
	}

	/**
	 * Projde kompletní rodokment a označí opakuící se psy
	 * @param array $completePedigree
	 */
	public function selectRepeatingDogs($pID, $fID, array $completePedigree) {
		$completePedigree[] = ['ID' => $pID];	// přidám psa
		$completePedigree[] = ['ID' => $fID];	// přidám fenu u kterých tu příbuznost ověřuji
		foreach ($completePedigree as $key => $values) {
			if ($this->isIdInPedigreeMoreTimes($completePedigree, $values['ID'])) {
				if (isset($this->colorByDogId[$values['ID']]) == false) {    // barvy pokud se pes vyskytuje v rodokemnu více krát
					$this->chooseColorForMoreOccurrencesDog($values['ID']);
				}
			}
		}
	}

	/**
	 * @param int $ID
	 * @param int $level
	 * @param int $max
	 * @param string $lang
	 */
	private function genealogDPTrace($ID,$level,$max, $lang) {
		global $pedigree;
		if ($level > $max) {
			return;
		};
		if ($ID != NULL) { // predek existuje
			$query = ["SELECT pes.ID AS ID, pes.Jmeno AS Jmeno, pes.oID AS oID, pes.mID AS mID, pes.Vyska AS Vyska, pes.Pohlavi As Pohlavi,
										pes.Plemeno As Plemeno,
										pes.Barva As BarvaOrder,
										plemeno.item as Varieta,
										pes.TitulyPredJmenem AS TitulyPredJmenem,
										pes.TitulyZaJmenem AS TitulyZaJmenem,
										barva.item AS Barva
										FROM appdata_pes as pes
										LEFT JOIN enum_item as plemeno
											ON (pes.Plemeno = plemeno.order && plemeno.enum_header_id = 7 && plemeno.lang = %s)
										LEFT JOIN enum_item as barva
											ON (pes.Barva = barva.order && barva.enum_header_id = 4 && barva.lang = %s)
										WHERE pes.ID = %i
										LIMIT 1", $lang, $lang, $ID];
			$row = $this->connection->query($query)->fetch()->toArray();
			
			$query = ["SELECT item as Nazev, Vysledek FROM appdata_zdravi as zdravi LEFT JOIN enum_item as ciselnik
				ON (ciselnik.enum_header_id = 14 AND ciselnik.order = zdravi.Typ) WHERE pID = %i ORDER BY Datum DESC", $row['ID']];
			$zdravi = $this->connection->query($query)->fetchPairs("Nazev","Vysledek");
			$zdravi = $zdravi === null ? '' : $zdravi;

			$query = ["SELECT Vysledek AS DKK FROM appdata_zdravi WHERE pID = %i && Typ=65 ORDER BY Datum DESC LIMIT 1", $row['ID']];
			$DKK = $this->connection->query($query)->fetch();
			$DKK = $DKK === false ? '' : $DKK->toArray()['DKK'];

			$query = ["SELECT Vysledek AS DLK FROM appdata_zdravi WHERE pID = %i && Typ=66 ORDER BY Datum DESC LIMIT 1", $row['ID']];
			$DLK = $this->connection->query($query)->fetch();
			$DLK = $DLK === false ? '' : $DLK->toArray()['DLK'];

			$pedigree[] = array(
				'Uroven' => $level,
				'ID' => $row['ID'],
				'Jmeno' => $this->arGet($row,'Jmeno'),
				'Barva' => $this->arGet($row,'Barva'),
				'Varieta' => $this->arGet($row,'Varieta'),
				'TitulyPredJmenem' => $this->arGet($row,'TitulyPredJmenem'),
				'TitulyZaJmenem' => $this->arGet($row,'TitulyZaJmenem'),
				'DKK' => $DKK,
				'DLK' => $DLK,
				'Vyska' => $this->arGet($row,'Vyska'),
				'zdravi' => $zdravi
			);

			$this->genealogDPTrace($row['oID'], $level + 1, $max, $lang);
			$this->genealogDPTrace($row['mID'], $level + 1, $max, $lang);
		} else { // predek neexistuje
			$pedigree[] = array(
				'Uroven' => $level,
				'ID' => NULL,
				'Jmeno' => '&nbsp;',
				'Barva' => '&nbsp;'
			);
			$this->genealogDPTrace(NULL, $level + 1, $max, $lang);
			$this->genealogDPTrace(NULL, $level + 1, $max, $lang);
		}
	}

	/**
	 * @param array $array
	 * @param string $name
	 * @return string
	 */
	private function arGet($array,$name) {
		if (isset($array[$name]) and trim($array[$name]) != '') {
			return ($array[$name]);
		} else {
			return('');
		}
	}

	/**
	 * Porovná pole tabulky rodokmenu a najde opakující se psy
	 * @param array $completePedigree
	 * @param $id
	 * @return bool
	 */
	private function isIdInPedigreeMoreTimes(array $completePedigree, $id) {
		$times = 0;
		foreach ($completePedigree as $level => $values) {
			if (isset($values['ID']) && ($values['ID'] == $id)) {
				$times += 1;
			}
		}

		return ($times > 1);
	}

	/**
	 * @param int $max
	 * @param Presenter $presenter
	 * @param int $ID
	 * @param bool $isUserAdmin
	 * @param bool $deepMark
	 * @return string
	 */
	private function genealogShowDeepPTable($max, Presenter $presenter, $ID, $isUserAdmin, $deepMark) {
		global $pedigree;
		$maxLevel = $max;
		$htmlOutput = "<table border='0' cellspacing='0' cellpadding='3' class='genTable'><tr>";
		$lastLevel = 0;
		for ($i = 0; $i < count($pedigree); $i++) {
			if ($pedigree[$i]['Uroven'] <= $lastLevel) {
				$htmlOutput .= '<tr>';
			}
			$lastLevel = $pedigree[$i]['Uroven'];
			$adds = array();
			if (isset($pedigree[$i]['Varieta']) && $pedigree[$i]['Varieta'] != '') {
				$adds[] = $pedigree[$i]['Varieta'];
			}
			if (isset($pedigree[$i]['Barva']) && $pedigree[$i]['Barva'] != '') {
				$adds[] = $pedigree[$i]['Barva'];
			}
			if (isset($pedigree[$i]['Vyska']) && $pedigree[$i]['Vyska'] != '0') {
				$adds[] = ($pedigree[$i]['Vyska']) . ' cm';
			}
			if (isset($pedigree[$i]['zdravi']) && $pedigree[$i]['zdravi'] != '') {
				foreach ($pedigree[$i]['zdravi'] as $zdraviTyp => $zdraviVysledek) {
					if (trim($zdraviVysledek) != "") {
						$adds[] = '' . $zdraviTyp . ': ' . $zdraviVysledek;
					}
				}
			}
			if (count($adds) > 0) {
				$adds = '<br />'.implode(', ',$adds);
			} else {
				$adds = '';
			}
			if (isset($pedigree[$i]['TitulyPredJmenem']) && trim($pedigree[$i]['TitulyPredJmenem']) != '') {
				$pedigree[$i]['Jmeno'] = $pedigree[$i]['TitulyPredJmenem'] . ' ' . $pedigree[$i]['Jmeno'];
			}
			if (isset($pedigree[$i]['TitulyZaJmenem']) && trim($pedigree[$i]['TitulyZaJmenem']) != '') {
				$pedigree[$i]['Jmeno'] = $pedigree[$i]['Jmeno'] . ', ' . $pedigree[$i]['TitulyZaJmenem'];
			}

			if ($pedigree[$i]['ID'] != NULL) {
				if(isset($this->avkTable[$pedigree[$i]['ID']])) {	// avk
					$this->avkTable[$pedigree[$i]['ID']] += + 1;
				} else {
					$this->avkTable[$pedigree[$i]['ID']] = 1;
				}
				$link = ($isUserAdmin ? $presenter->link('FeItem1velord2:edit', $pedigree[$i]['ID']) : $presenter->link('FeItem1velord2:view', $pedigree[$i]['ID']));
				if ($deepMark && isset($this->colorByDogId[$pedigree[$i]['ID']])) { //in_array($pedigree[$i]['ID'], $deepMarkArray)) { stará logika
					$htmlOutput .= '<td rowspan="'.pow(2,$maxLevel - $pedigree[$i]['Uroven'] ).'" style="background-color:'.$this->colorByDogId[$pedigree[$i]['ID']].'">'
					. '<b><a href="' . $link . '">'.$pedigree[$i]['Jmeno'].'</a></b>' . ($deepMark ? "" : $adds) . '</td>';
				} else {
					$htmlOutput .= '<td rowspan="'.pow(2,$maxLevel - $pedigree[$i]['Uroven'] ).'">
					<b><a href="' . $link . '">'.$pedigree[$i]['Jmeno'].'</a></b>' . ($deepMark ? "" :  $adds) . '</td>';
				}
				if (($pedigree[$i]['Uroven'] > 0) && ($pedigree[$i]['Uroven'] != $maxLevel)) {
					$this->setLastPredecessorSession($pedigree[$i]['Uroven'], $pedigree[$i]['ID']);
					$this->setLastPredecessorSession($pedigree[$i]['Uroven'] . 'Uroven', $pedigree[$i]['Uroven']);
				}
			} else {
				$htmlOutput .= '<td rowspan="' . pow(2, $maxLevel - $pedigree[$i]['Uroven']) . '">';
				if ($isUserAdmin) {
					if ($pedigree[$i]['Uroven'] == 1) {
						$htmlOutput .= '<a href="' . $presenter->link("FeItem1velord2:addMissingDog", $ID) . '">' . DOG_FORM_PEDIGREE_ADD_MISSING . '</a>';
					} else {
						if ($this->getLastPredecessorSession($pedigree[$i]['Uroven'] - 1) != null) {
							$htmlOutput .= '<a href="' . $presenter->link("FeItem1velord2:addMissingDog", $this->getLastPredecessorSession($pedigree[$i]['Uroven'] - 1)) . '">' . DOG_FORM_PEDIGREE_ADD_MISSING . '</a>';
						}
					}
					for ($y = ($pedigree[$i]['Uroven']); $y <= $maxLevel; $y++) {	// jakmile jsem tady už jednou vypsal zbylá data nepotřebuji
						$this->clearPedigreeKey($y);
						$this->clearPedigreeKey($y . 'Uroven');
					}
					$htmlOutput .= '</td>'; // <b>' . $pedigree[$i]['Jmeno'].'</b><br/> '.$adds.'</td>';
				}
				if ($pedigree[$i]['Uroven'] == $maxLevel) {
					$htmlOutput .= '</tr>';
				}
			}
		}
		$htmlOutput .= "</table>";

		return $htmlOutput;
	}

	/**
	 * Vybere barvu pro opakující se psy
	 * @param int $dogId
	 */
	private function chooseColorForMoreOccurrencesDog($dogId) {
		$rand = 0;
		while (isset($this->backgroundColors[$rand]) == false){
			$rand += 1;
		}
		$this->colorByDogId[$dogId] = $this->backgroundColors[$rand];
		unset($this->backgroundColors[$rand]);
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private function getLastPredecessorSession($key) {
		$return = "";
		if ($this->session->hasSection(self::SESSION_LAST_PREDECESSOR)) {
			$section = $this->session->getSection(self::SESSION_LAST_PREDECESSOR);
			$return = $section->{$key};
		}

		return $return;
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	private function setLastPredecessorSession($key, $value) {
		$section = $this->session->getSection(self::SESSION_LAST_PREDECESSOR);
		$section->{$key} = $value;
	}

	/**
	 * Vyčistím sešnu
	 */
	private function clearPedigreeKey($wantToDeleteKey) {
		// odstraním posledně použite mID a oID u rokomene
		$section = $this->session->getSection(self::SESSION_LAST_PREDECESSOR);
		foreach($section as $key => $value) {
			if ($wantToDeleteKey == $key) {
				unset($section->{$key});
			}
		}
	}

	/**
	 * Vyčistím sešnu
	 */
	private function clearPedigreeSession() {
		// odstraním posledně použite mID a oID u rokomene
		$section = $this->session->getSection(self::SESSION_LAST_PREDECESSOR);
		foreach($section as $key => $value) {
			unset($section->{$key});
		}
	}
}
