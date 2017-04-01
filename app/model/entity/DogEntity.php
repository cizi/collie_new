<?php

namespace App\Model\Entity;


use Dibi\DateTime;

class DogEntity {

	/** @var int */
	private $ID;

	/** @var string */
	private $TitulyPredJmenem;

	/** @var string */
	private $TitulyZaJmenem;

	/** @var string */
	private $Jmeno;

	/** @var DateTime */
	private $DatNarozeni;

	/** @var DateTime */
	private $DatUmrti;

	/** @var string */
	private $UmrtiKomentar;

	/** @var int */
	private $Pohlavi;

	/** @var int */
	private $Plemeno;

	/** @var int */
	private $Barva;

	/** @var int */
	private $Srst;

	/** @var string */
	private $BarvaKomentar;

	/** @var string */
	private $CisloZapisu;

	/** @var string */
	private $PCisloZapisu;

	/** @var string */
	private $Cip;

	/** @var string */
	private $Tetovani;

	/** @var string */
	private $ZdravotniKomentar;

	/** @var int */
	private $Varlata;

	/** @var int */
	private $Skus;

	/** @var int */
	private $Zuby;

	/** @var string */
	private $ZubyKomentar;

	/** @var int */
	private $Chovnost;

	/** @var string */
	private $ChovnyKomentar;

	/** @var string */
	private $Posudek;

	/** @var string */
	private $Zkousky;

	/** @var string */
	private $TitulyKomentar;

	/** @var string */
	private $Oceneni;

	/** @var  string */
	private $Zavody;

	/** @var int */
	private $oID;

	/** @var int */
	private $mID;

	/** @var  string */
	private $Komentar;

	/** @var  DateTime */
	private $PosledniZmena;

	/** @var  int */
	private $Vyska;

	/** @var  int */
	private $Vaha;

	/** @var  string */
	private $Bonitace;

	/** @var  string */
	private $ImpFrom;

	/** @var  int */
	private $ImpID;

	/** @var  int */
	private $oIDupdate;

	/** @var  int */
	private $mIDupdate;

	/**
	 * @return int
	 */
	public function getID() {
		return $this->ID;
	}

	/**
	 * @param int $ID
	 */
	public function setID($ID) {
		$this->ID = $ID;
	}

	/**
	 * @return string
	 */
	public function getTitulyPredJmenem() {
		return $this->TitulyPredJmenem;
	}

	/**
	 * @param string $TitulyPredJmenem
	 */
	public function setTitulyPredJmenem($TitulyPredJmenem) {
		$this->TitulyPredJmenem = $TitulyPredJmenem;
	}

	/**
	 * @return string
	 */
	public function getTitulyZaJmenem() {
		return $this->TitulyZaJmenem;
	}

	/**
	 * @param string $TitulyZaJmenem
	 */
	public function setTitulyZaJmenem($TitulyZaJmenem) {
		$this->TitulyZaJmenem = $TitulyZaJmenem;
	}

	/**
	 * @return string
	 */
	public function getJmeno() {
		return $this->Jmeno;
	}

	/**
	 * @param string $Jmeno
	 */
	public function setJmeno($Jmeno) {
		$this->Jmeno = $Jmeno;
	}

	/**
	 * @return DateTime
	 */
	public function getDatNarozeni() {
		return $this->DatNarozeni;
	}

	/**
	 * @param DateTime $DatNarozeni
	 */
	public function setDatNarozeni($DatNarozeni) {
		$this->DatNarozeni = $DatNarozeni;
	}

	/**
	 * @return DateTime
	 */
	public function getDatUmrti() {
		return $this->DatUmrti;
	}

	/**
	 * @param DateTime $DatUmrti
	 */
	public function setDatUmrti($DatUmrti) {
		$this->DatUmrti = $DatUmrti;
	}

	/**
	 * @return string
	 */
	public function getUmrtiKomentar() {
		return $this->UmrtiKomentar;
	}

	/**
	 * @param string $UmrtiKomentar
	 */
	public function setUmrtiKomentar($UmrtiKomentar) {
		$this->UmrtiKomentar = $UmrtiKomentar;
	}

	/**
	 * @return int
	 */
	public function getPohlavi() {
		return $this->Pohlavi;
	}

	/**
	 * @param int $Pohlavi
	 */
	public function setPohlavi($Pohlavi) {
		$this->Pohlavi = $Pohlavi;
	}

	/**
	 * @return int
	 */
	public function getPlemeno() {
		return $this->Plemeno;
	}

	/**
	 * @param int $Plemeno
	 */
	public function setPlemeno($Plemeno) {
		$this->Plemeno = $Plemeno;
	}

	/**
	 * @return int
	 */
	public function getBarva() {
		return $this->Barva;
	}

	/**
	 * @param int $Barva
	 */
	public function setBarva($Barva) {
		$this->Barva = $Barva;
	}

	/**
	 * @return int
	 */
	public function getSrst() {
		return $this->Srst;
	}

	/**
	 * @param int $Srst
	 */
	public function setSrst($Srst) {
		$this->Srst = $Srst;
	}

	/**
	 * @return string
	 */
	public function getBarvaKomentar() {
		return $this->BarvaKomentar;
	}

	/**
	 * @param string $BarvaKomentar
	 */
	public function setBarvaKomentar($BarvaKomentar) {
		$this->BarvaKomentar = $BarvaKomentar;
	}

	/**
	 * @return string
	 */
	public function getCisloZapisu() {
		return $this->CisloZapisu;
	}

	/**
	 * @param string $CisloZapisu
	 */
	public function setCisloZapisu($CisloZapisu) {
		$this->CisloZapisu = $CisloZapisu;
	}

	/**
	 * @return string
	 */
	public function getPCisloZapisu() {
		return $this->PCisloZapisu;
	}

	/**
	 * @param string $PCisloZapisu
	 */
	public function setPCisloZapisu($PCisloZapisu) {
		$this->PCisloZapisu = $PCisloZapisu;
	}

	/**
	 * @return string
	 */
	public function getCip() {
		return $this->Cip;
	}

	/**
	 * @param string $Cip
	 */
	public function setCip($Cip) {
		$this->Cip = $Cip;
	}

	/**
	 * @return string
	 */
	public function getTetovani() {
		return $this->Tetovani;
	}

	/**
	 * @param string $Tetovani
	 */
	public function setTetovani($Tetovani) {
		$this->Tetovani = $Tetovani;
	}

	/**
	 * @return string
	 */
	public function getZdravotniKomentar() {
		return $this->ZdravotniKomentar;
	}

	/**
	 * @param string $ZdravotniKomentar
	 */
	public function setZdravotniKomentar($ZdravotniKomentar) {
		$this->ZdravotniKomentar = $ZdravotniKomentar;
	}

	/**
	 * @return int
	 */
	public function getVarlata() {
		return $this->Varlata;
	}

	/**
	 * @param int $Varlata
	 */
	public function setVarlata($Varlata) {
		$this->Varlata = $Varlata;
	}

	/**
	 * @return int
	 */
	public function getSkus() {
		return $this->Skus;
	}

	/**
	 * @param int $Skus
	 */
	public function setSkus($Skus) {
		$this->Skus = $Skus;
	}

	/**
	 * @return string
	 */
	public function getZuby() {
		return $this->Zuby;
	}

	/**
	 * @param string $Zuby
	 */
	public function setZuby($Zuby) {
		$this->Zuby = $Zuby;
	}

	/**
	 * @return string
	 */
	public function getZubyKomentar() {
		return $this->ZubyKomentar;
	}

	/**
	 * @param string $ZubyKomentar
	 */
	public function setZubyKomentar($ZubyKomentar) {
		$this->ZubyKomentar = $ZubyKomentar;
	}

	/**
	 * @return int
	 */
	public function getChovnost() {
		return $this->Chovnost;
	}

	/**
	 * @param int $Chovnost
	 */
	public function setChovnost($Chovnost) {
		$this->Chovnost = $Chovnost;
	}

	/**
	 * @return string
	 */
	public function getChovnyKomentar() {
		return $this->ChovnyKomentar;
	}

	/**
	 * @param string $ChovnyKomentar
	 */
	public function setChovnyKomentar($ChovnyKomentar) {
		$this->ChovnyKomentar = $ChovnyKomentar;
	}

	/**
	 * @return string
	 */
	public function getPosudek() {
		return $this->Posudek;
	}

	/**
	 * @param string $Posudek
	 */
	public function setPosudek($Posudek) {
		$this->Posudek = $Posudek;
	}

	/**
	 * @return string
	 */
	public function getZkousky() {
		return $this->Zkousky;
	}

	/**
	 * @param string $Zkousky
	 */
	public function setZkousky($Zkousky) {
		$this->Zkousky = $Zkousky;
	}

	/**
	 * @return string
	 */
	public function getTitulyKomentar() {
		return $this->TitulyKomentar;
	}

	/**
	 * @param string $TitulyKomentar
	 */
	public function setTitulyKomentar($TitulyKomentar) {
		$this->TitulyKomentar = $TitulyKomentar;
	}

	/**
	 * @return string
	 */
	public function getOceneni() {
		return $this->Oceneni;
	}

	/**
	 * @param string $Oceneni
	 */
	public function setOceneni($Oceneni) {
		$this->Oceneni = $Oceneni;
	}

	/**
	 * @return string
	 */
	public function getZavody() {
		return $this->Zavody;
	}

	/**
	 * @param string $Zavody
	 */
	public function setZavody($Zavody) {
		$this->Zavody = $Zavody;
	}

	/**
	 * @return int
	 */
	public function getOID() {
		return $this->oID;
	}

	/**
	 * @param int $oID
	 */
	public function setOID($oID) {
		$this->oID = $oID;
	}

	/**
	 * @return int
	 */
	public function getMID() {
		return $this->mID;
	}

	/**
	 * @param int $mID
	 */
	public function setMID($mID) {
		$this->mID = $mID;
	}

	/**
	 * @return string
	 */
	public function getKomentar() {
		return $this->Komentar;
	}

	/**
	 * @param string $Komentar
	 */
	public function setKomentar($Komentar) {
		$this->Komentar = $Komentar;
	}

	/**
	 * @return DateTime
	 */
	public function getPosledniZmena() {
		return $this->PosledniZmena;
	}

	/**
	 * @param \Nette\Utils\DateTime $PosledniZmena
	 */
	public function setPosledniZmena($PosledniZmena) {
		$this->PosledniZmena = $PosledniZmena;
	}

	/**
	 * @return int
	 */
	public function getVyska() {
		return $this->Vyska;
	}

	/**
	 * @param int $Vyska
	 */
	public function setVyska($Vyska) {
		$this->Vyska = $Vyska;
	}

	/**
	 * @return int
	 */
	public function getVaha() {
		return $this->Vaha;
	}

	/**
	 * @param int $Vaha
	 */
	public function setVaha($Vaha) {
		$this->Vaha = $Vaha;
	}

	/**
	 * @return string
	 */
	public function getBonitace() {
		return $this->Bonitace;
	}

	/**
	 * @param string $Bonitace
	 */
	public function setBonitace($Bonitace) {
		$this->Bonitace = $Bonitace;
	}

	/**
	 * @return string
	 */
	public function getImpFrom() {
		return $this->ImpFrom;
	}

	/**
	 * @param string $ImpFrom
	 */
	public function setImpFrom($ImpFrom) {
		$this->ImpFrom = $ImpFrom;
	}

	/**
	 * @return int
	 */
	public function getImpID() {
		return $this->ImpID;
	}

	/**
	 * @param int $ImpID
	 */
	public function setImpID($ImpID) {
		$this->ImpID = $ImpID;
	}

	/**
	 * @return int
	 */
	public function getOIDupdate() {
		return $this->oIDupdate;
	}

	/**
	 * @param int $oIDupdate
	 */
	public function setOIDupdate($oIDupdate) {
		$this->oIDupdate = $oIDupdate;
	}

	/**
	 * @return int
	 */
	public function getMIDupdate() {
		return $this->mIDupdate;
	}

	/**
	 * @param int $mIDupdate
	 */
	public function setMIDupdate($mIDupdate) {
		$this->mIDupdate = $mIDupdate;
	}

	public function hydrate(array $data) {
		$this->setID(isset($data['ID']) ? $data['ID'] : null);
		$this->setTitulyPredJmenem(isset($data['TitulyPredJmenem']) ? $data['TitulyPredJmenem'] : null);
		$this->setTitulyZaJmenem(isset($data['TitulyZaJmenem']) ? $data['TitulyZaJmenem'] : null);
		$this->setJmeno(isset($data['Jmeno']) ? $data['Jmeno'] : null);
		$this->setDatNarozeni(isset($data['DatNarozeni']) ? $data['DatNarozeni'] : null);
		$this->setDatUmrti(isset($data['DatUmrti']) ? $data['DatUmrti'] : null);
		$this->setUmrtiKomentar(isset($data['UmrtiKomentar']) ? $data['UmrtiKomentar'] : null);
		$this->setPohlavi(isset($data['Pohlavi']) ? $data['Pohlavi'] : null);
		$this->setPlemeno(isset($data['Plemeno']) ? $data['Plemeno'] : null);
		$this->setBarva(isset($data['Barva']) ? $data['Barva'] : null);
		$this->setSrst(isset($data['Srst']) ? $data['Srst'] : null);
		$this->setBarvaKomentar(isset($data['BarvaKomentar']) ? $data['BarvaKomentar'] : null);
		$this->setCisloZapisu(isset($data['CisloZapisu']) ? $data['CisloZapisu'] : null);
		$this->setPCisloZapisu(isset($data['PCisloZapisu']) ? $data['PCisloZapisu'] : null);
		$this->setCip(isset($data['Cip']) ? $data['Cip'] : null);
		$this->setTetovani(isset($data['Tetovani']) ? $data['Tetovani'] : null);
		$this->setZdravotniKomentar(isset($data['ZdravotniKomentar']) ? $data['ZdravotniKomentar'] : null);
		$this->setVarlata(isset($data['Varlata']) ? $data['Varlata'] : null);
		$this->setSkus(isset($data['Skus']) ? $data['Skus'] : null);
		$this->setZuby(isset($data['Zuby']) ? $data['Zuby'] : null);
		$this->setZubyKomentar(isset($data['ZubyKomentar']) ? $data['ZubyKomentar'] : null);
		$this->setChovnost(isset($data['Chovnost']) ? $data['Chovnost'] : null);
		$this->setChovnyKomentar(isset($data['ChovnyKomentar']) ? $data['ChovnyKomentar'] : null);
		$this->setPosudek(isset($data['Posudek']) ? $data['Posudek'] : null);
		$this->setZkousky(isset($data['Zkousky']) ? $data['Zkousky'] : null);
		$this->setTitulyKomentar(isset($data['TitulyKomentar']) ? $data['TitulyKomentar'] : null);
		$this->setOceneni(isset($data['Oceneni']) ? $data['Oceneni'] : null);
		$this->setZavody(isset($data['Zavody']) ? $data['Zavody'] : null);
		$this->setoID(isset($data['oID']) ? $data['oID'] : null);
		$this->setmID(isset($data['mID']) ? $data['mID'] : null);
		$this->setKomentar(isset($data['Komentar']) ? $data['Komentar'] : null);
		$this->setPosledniZmena(isset($data['PosledniZmena']) ? $data['PosledniZmena'] : null);
		$this->setVyska(isset($data['Vyska']) ? $data['Vyska'] : null);
		$this->setVaha(isset($data['Vaha']) ? $data['Vaha'] : null);
		$this->setBonitace(isset($data['Bonitace']) ? $data['Bonitace'] : null);
		$this->setImpFrom(isset($data['ImpFrom']) ? $data['ImpFrom'] : null);
		$this->setImpID(isset($data['ImpID']) ? $data['ImpID'] : null);
		$this->setoIDupdate(isset($data['oIDupdate']) ? $data['oIDupdate'] : null);
		$this->setmIDupdate(isset($data['mIDupdate']) ? $data['mIDupdate'] : null);
	}

	public function extract() {
		return [
			'ID' => $this->getID(),
			'TitulyPredJmenem' => $this->getTitulyPredJmenem(),
			'TitulyZaJmenem' => $this->getTitulyZaJmenem(),
			'Jmeno' => $this->getJmeno(),
			'DatNarozeni' => $this->getDatNarozeni(),
			'DatUmrti' => $this->getDatUmrti(),
			'UmrtiKomentar' => $this->getUmrtiKomentar(),
			'Pohlavi' => $this->getPohlavi(),
			'Plemeno' => $this->getPlemeno(),
			'Barva' => $this->getBarva(),
			'Srst' => $this->getSrst(),
			'BarvaKomentar' => $this->getBarvaKomentar(),
			'CisloZapisu' => $this->getCisloZapisu(),
			'PCisloZapisu' => $this->getPCisloZapisu(),
			'Cip' => $this->getCip(),
			'Tetovani' => $this->getTetovani(),
			'ZdravotniKomentar' => $this->getZdravotniKomentar(),
			'Varlata' => $this->getVarlata(),
			'Skus' => $this->getSkus(),
			'Zuby' => $this->getZuby(),
			'ZubyKomentar' => $this->getZubyKomentar(),
			'Chovnost' => $this->getChovnost(),
			'ChovnyKomentar' => $this->getChovnyKomentar(),
			'Posudek' => $this->getPosudek(),
			'Zkousky' => $this->getZkousky(),
			'TitulyKomentar' => $this->getTitulyKomentar(),
			'Oceneni' => $this->getOceneni(),
			'Zavody' => $this->getZavody(),
			'oID' => $this->getoID(),
			'mID' => $this->getmID(),
			'Komentar' => $this->getKomentar(),
			'PosledniZmena' => $this->getPosledniZmena(),
			'Vyska' => $this->getVyska(),
			'Vaha' => $this->getVaha(),
			'Bonitace' => $this->getBonitace(),
			'ImpFrom' => $this->getImpFrom(),
			'ImpID' => $this->getImpID(),
			'oIDupdate' => $this->getoIDupdate(),
			'mIDupdate' => $this->getmIDupdate()
		];
	}
}