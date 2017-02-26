<?php

namespace App\Model;

use App\Model\Entity\EnumerationEntity;
use App\Model\Entity\EnumerationItemEntity;

class EnumerationRepository extends BaseRepository {

	/**
	 * @param EnumerationEntity $enumerationEntity
	 */
	public function createEnum(EnumerationEntity $enumerationEntity) {
		$this->connection->begin();
		try {
			$query = "insert into enum_header (description) values (USER ENUM)";
			$this->connection->query($query);

			$newEnumId = $this->connection->getInsertId();
			$enumerationEntity->setEnumHeaderId($newEnumId);
			$query = ["insert into enum_translation", $enumerationEntity->extract()];
			$this->connection->query($query);

			foreach($enumerationEntity->getItems() as $enumItem) {
				$enumItem->setEnumHeaderId($newEnumId);
				$query = ["insert into enum_item", $enumItem->extract()];
				$this->connection->query($query);
			}
		} catch (\Exception $e) {
			$this->connection->rollback();
		}
		$this->connection->commit();
	}

	/**
	 * @param int $id
	 */
	public function deleteEnum($id) {
		$this->connection->begin();
		try {
			$query = ["delete * from enum_item where enum_header_id = %d", $id];
			$this->connection->query($query);

			$query = ["delete * from enum_translation where enum_header_id = %d", $id];
			$this->connection->query($query);

			$query = ["delete * from enum_header where id = %d", $id];
			$this->connection->query($query);
		} catch (\Exception $e) {
			$this->connection->rollback();
		}
		$this->connection->commit();
	}

	/**
	 * @param string $lang
	 * @return array
	 */
	public function findEnums($lang) {
		$return = [];
		$query = ["select et.lang, et.description, et.enum_header_id, eh.id from enum_header as eh
				left join enum_translation as et on eh.id = et.enum_header_id
				where lang = %s",
			$lang];

		$result = $this->connection->query($query)->fetchAll();
		foreach ($result as $item) {
			$enum = new EnumerationEntity();
			$enum->hydrate($item->toArray());
			$enum->setItems($this->findEnumItems($lang, $enum->getEnumHeaderId()));
			$return[] = $enum;
		}

		return $return;
	}

	/**
	 * @param int $id
	 * @param string $lang
	 * @return EnumerationEntity
	 */
	public function getEnumDescription($id, $lang) {
		$query = ["select et.lang, et.description, et.enum_header_id from enum_translation as et
				where et.enum_header_id = %i and lang = %s",
			$id,
			$lang
		];

		$result = $this->connection->query($query)->fetch();
		$enum = new EnumerationEntity();
		$enum->hydrate($result->toArray());

		return $enum;
	}

	/**
	 * @param string $lang
	 * @param int $enumHeaderId
	 * @return array
	 */
	public function findEnumItems($lang, $enumHeaderId) {
		$return = [];
		$query = ["select * from enum_item where enum_header_id = %i and lang = %s", $enumHeaderId, $lang];
		$result = $this->connection->query($query)->fetchAll();
		foreach ($result as $item) {
			$enumItem = new EnumerationItemEntity();
			$enumItem->hydrate($item->toArray());
			$return[] = $enumItem;
		}

		return $return;
	}

	/**
	 * @param string $lang
	 * @param int $enumHeaderId
	 * @return array
	 */
	public function findEnumItemsByOrder($order) {
		$return = [];
		$query = ["select * from enum_item where `order` = %i", $order];
		$result = $this->connection->query($query)->fetchAll();
		foreach ($result as $item) {
			$enumItem = new EnumerationItemEntity();
			$enumItem->hydrate($item->toArray());
			$return[] = $enumItem;
		}

		return $return;
	}

	/**
	 * Sma�e hodnotu ��seln�ku
	 * @param int $headerId
	 * @param int $order
	 */
	public function deleteEnumItem($headerId, $order) {
		$query = ["delete from enum_item where `order` = %i and enum_header_id = %i", $order, $headerId];
		$this->connection->query($query);
	}

	public function saveEnumerationItems(array $items) {
		$result = true;
		$this->connection->begin();
		try {
			$query = "select max(`order`) from enum_item";
			$newOrder = $this->connection->query($query)->fetchSingle() + 1;
			/** @var EnumerationItemEntity $item */
			foreach($items as $item) {
				if ($item->getOrder() == null) {
					$item->setOrder($newOrder);
					$query = ["insert into enum_item", $item->extract()];
				} else {
					$query = ["update enum_item set item = %s where id = %i", $item->getItem(), $item->getId()];
				}
				$this->connection->query($query);
			}
		} catch (\Exception $e) {
			$this->connection->rollback();
			$result = false;
		}
		$this->connection->commit();

		return $result;
	}

	/**
	 * @param string $lang
	 * @param int $enumHeaderId
	 * @return array
	 */
	public function findEnumItemByOrder($lang, $enumHeaderId, $order) {
		$query = ["select * from enum_item where enum_header_id = %i and lang = %s and `order` = %i", $enumHeaderId, $lang, $order];
		$result = $this->connection->query($query)->fetch();
			$enumItem = new EnumerationItemEntity();
			$enumItem->hydrate($result->toArray());

		return $enumItem->getItem();
	}
}