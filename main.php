<?php

class Barn {
	public static $collectionsTotal = 0;

	public static $quantity = 0;
	public $id = "UnregisteredProperty";

	public $livestock = array();
	public $livestockByType = array();
	public $lastYield = array();
	public $collections = 0;

	public function __construct() {
		$this->id = "barn_" . strval(self::$quantity);
		self::$quantity += 1;
		echo "Мы приобрели хлев и дали ему регистрационный номер " . $this->id . "\n";
	}

	public function collectGoods() {
		global $goods;
		global $livestockNamesRu;
		global $farmYield;

		self::$collectionsTotal += 1;
		$this->collections += 1;
		$this->lastYield = array();

		foreach ($this->livestock as $animal) {
			$goodKey = (string) $animal->getGood();

			$yield = $animal->collectGood();
			
			if (!isset($this->lastYield[$goodKey])) {
				$this->lastYield[$goodKey] = 0;
			}
			$this->lastYield[$goodKey] += $yield;
			
			if (!isset($farmYield[$goodKey])) {
				$farmYield[$goodKey] = 0;
			}
			$farmYield[$goodKey] += $yield;
		}

		// echo "\nМы пошли собирать продукцию из хлева " . $this . " в " . $this->collections . "-й раз (всего " . self::$collectionsTotal . ")\n";
		echo "\nМы пошли собирать продукцию из хлева " . $this . " (сбор №" . self::$collectionsTotal . ")";
		echo "\nВсего животных на ферме: |";
		foreach ($this->livestockByType as $animalKey => $quantity) {
			echo $livestockNamesRu[$animalKey] . " - " . $quantity . "|";
		}
		echo "\nСписок собранного: |";
		foreach ($this->lastYield as $goodKey => $yield) {
			echo $goods[$goodKey]->getNameRu() . " - " . $yield . "" . $goods[$goodKey]->getUnitRu() . "|";
		}
		echo "\n";
	}

    public function __toString()
    {
        return $this->id;
    }
}

abstract class BarnAnimal {
	public static $quantity = 0;
	public $id = "UnregisteredProperty";
	public $barn;

	public function __construct() {
		$this->id = $this->getKey() . "_" . strval(self::$quantity);
		self::$quantity += 1;
		// echo "Мы приобрели животное (". $this->getNameRu() .") и дали ему регистрационный номер " . $this->id . "\n";
	}

	public function registerAtBarn($barn) {
		global $farmLivestock;
		global $farmLivestockByType;
		array_push($barn->livestock, $this);
		array_push($farmLivestock, $this);

		$animalKey = $this->getKey();

		if (!isset($barn->livestockByType[$animalKey])) {
			$barn->livestockByType[$animalKey] = 0;
		}
		$barn->livestockByType[$animalKey] += 1;

		$animalKey = $this->getKey();
		if (!isset($farmLivestockByType[$animalKey])) {
			$farmLivestockByType[$animalKey] = 0;
		}
		$farmLivestockByType[$animalKey] += 1;

		$this->barn = $barn;
		echo "Мы зарегистрировали животное (" . $this->getNameRu() . ", ". $this .") в хлеву " . $this->barn . "\n";
	}

	abstract public static function getKey();
	abstract public function getNameRu();
	abstract public function getGood();
	abstract public function collectGood();

    public function __toString()
    {
        return $this->id;
    }
}

class BarnAnimalCow extends BarnAnimal {
	public static function getKey() {
		return "cow";
	}
	
	public function getNameRu() {
		return "Корова";
	}

	public function getGood() {
		global $goods;
		return $goods["milk"];
	}

	public function collectGood() {
		return rand(8, 12);
	}
}

class BarnAnimalChicken extends BarnAnimal {
	public static function getKey() {
		return "chicken";
	}

	public function getNameRu() {
		return "Курица";
	}

	public function getGood() {
		global $goods;
		return $goods["egg"];
	}

	public function collectGood() {
		return rand(0, 1);
	}
}

abstract class BarnAnimalGood {
	abstract public static function getKey();
	abstract public function getNameRu();
	abstract public function getUnitRu();

    public function __toString()
    {
        return $this->getKey();
    }
}

class BarnAnimalGoodMilk extends BarnAnimalGood {
	public static function getKey() {
		return "milk";
	}
	
	public function getNameRu() {
		return "Молоко";
	}

	public function getUnitRu() {
		return " л.";
	}
}

class BarnAnimalGoodEgg extends BarnAnimalGood {
	public static function getKey() {
		return "egg";
	}

	public function getNameRu() {
		return "Яйцо";
	}

	public function getUnitRu() {
		return " шт.";
	}
}

echo "\n\n";

$goods = array(
	BarnAnimalGoodMilk::getKey()=>new BarnAnimalGoodMilk(),
	BarnAnimalGoodEgg::getKey()=>new BarnAnimalGoodEgg()
);

$livestockNamesRu = array(
	BarnAnimalCow::getKey()=>BarnAnimalCow::getNameRu(),
	BarnAnimalChicken::getKey()=>BarnAnimalChicken::getNameRu()
);

$barns = array();

$farmYield = array();
$farmLivestock = array();
$farmLivestockByType = array();

$BARNS_QUANTITY = 1;
// $BARNS_QUANTITY = rand(1, 5); // debug

for ($i = 0; $i < $BARNS_QUANTITY; $i++) {
	$barn = new Barn();
	array_push($barns, $barn);
	
	$cowQuantity = 10;
	// $cowQuantity = rand(5, 15); // debug
	$chickenQuantity = 20;
	// $chickenQuantity = rand(15, 25); // debug
	
	for ($i2 = 0; $i2 < $cowQuantity; $i2++) {
		$animal = new BarnAnimalCow();
		$animal->registerAtBarn($barn);
	}
	for ($i2 = 0; $i2 < $chickenQuantity; $i2++) {
		$animal = new BarnAnimalChicken();
		$animal->registerAtBarn($barn);
	}
}

foreach ($barns as $barn) {
	$collections = 1;
	// $collections = rand(1, 10); // debug
	for ($i = 0; $i < $collections; $i++) {
		$barn->collectGoods();
	}
}

echo "\n\nИтог сбора. Кол-во хлевов: " . count($barns) . "\n";
echo "Всего животных на ферме: |";
foreach ($farmLivestockByType as $animalKey => $quantity) {
	echo $livestockNamesRu[$animalKey] . " - " . $quantity . "|";
}
echo "\nСобранные с них ресурсы: |";
foreach ($farmYield as $goodKey => $yield) {
	echo $goods[$goodKey]->getNameRu() . " - " . $yield . "" . $goods[$goodKey]->getUnitRu() . "|";
}
echo "\n";

echo "\n\n";
?>

