<?php
//==================================================
//Ферма OOP [main.php]
//Разработал Timur Moziev (code@timurrin.ru) (2021-02-26)
//>>>> Имплементация механизма фермы в object-oriented programming (OOP), тестовое задание для компании Oxem Studio
//==================================================

class Barn {
	public static $quantity = 0; // количество хлевов для образования уникального ID
	public $id; // регистрационный номер хлева

	public $livestock = array(); // indexed-массив со всеми животными хлева
	public $livestockByType = array(); // аssociative-массив с количеством животных хлева по их типу

	// public $collections = 0; // хранит количество сборов продукции с этого хлева
	public static $collectionsTotal = 0; // хранит общее количество сборов продукции со всех хлевов

	public function __construct() {
		// Несмотря на то, что ТЗ предусматривает лишь один хлев, мы всё равно делаем ему отдельный класс и присваиваем идентификатор
		// Реализация на данный момент поддерживает несколько хлевов
		$this->id = "barn_" . strval(self::$quantity);
		self::$quantity += 1;
		echo "Мы приобрели хлев и дали ему регистрационный номер " . $this->id . "\n";
	}

	// Функция собирает ресурсы с данного хлева
	public function collectGoods() {
		global $goods; // для красвого вывода на русском языке
		global $livestockNamesRu; // для красвого вывода на русском языке
		global $farmYield; // для общей статистики сбора продукции

		self::$collectionsTotal += 1;
		// $this->collections += 1;

		$currentYield = array(); // в этот массив соберём информацию о ресурсах с этого хлева для показа в конце функции

		foreach ($this->livestock as $animal) {
			$goodKey = (string) $animal->getGood();

			// получаем количество продукции с каждого животного и записываем в статистику

			$yield = $animal->collectGood();

			if (!isset($currentYield[$goodKey])) {
				$currentYield[$goodKey] = 0;
			}
			$currentYield[$goodKey] += $yield;

			if (!isset($farmYield[$goodKey])) {
				$farmYield[$goodKey] = 0;
			}
			$farmYield[$goodKey] += $yield;
		}

		// отображаем статистику хлева

		// echo "\nМы пошли собирать продукцию из хлева " . $this . " в " . $this->collections . "-й раз (всего " . self::$collectionsTotal . ")\n";
		echo "\nМы пошли собирать продукцию из хлева " . $this . " (сбор №" . self::$collectionsTotal . ")";
		echo "\nЖивотных в хлеву: |";
		foreach ($this->livestockByType as $animalKey => $quantity) {
			echo $livestockNamesRu[$animalKey] . " - " . $quantity . "|";
		}
		echo "\nСписок собранного: |";
		foreach ($currentYield as $goodKey => $yield) {
			echo $goods[$goodKey]->getNameRu() . " - " . $yield . "" . $goods[$goodKey]->getUnitRu() . "|";
		}
		echo "\n";
	}

    public function __toString()
    {
        return $this->id;
    }
}

// абстрактный класс для животных, на него основе создаём класс коровы и куры
abstract class BarnAnimal {
	public static $quantity = 0;
	public $id;
	public $barn;

	public function __construct() {
		// присваиваем идентификатор животному
		$this->id = $this->getKey() . "_" . strval(self::$quantity);
		self::$quantity += 1;
		// echo "Мы приобрели животное (". $this->getNameRu() .") и дали ему регистрационный номер " . $this->id . "\n";
	}

	// функция регистрирует животное в определённом хлеве
	public function registerAtBarn($barn) {
		global $farmLivestock; // для общего количества животных на ферме
		global $farmLivestockByType; // для статистики кол-ва по типу животного

		array_push($barn->livestock, $this); // добавляем в массив хлева
		array_push($farmLivestock, $this); // добавляем в массив фермы

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

		echo "Мы зарегистрировали животное (" . $this->getNameRu() . ", ". $this .") в хлев " . $this->barn . "\n";
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
		return BarnAnimalGoodMilk::getKey();
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
		return "Кура";
	}

	public function getGood() {
		return BarnAnimalGoodEgg::getKey();
	}

	public function collectGood() {
		return rand(0, 1);
	}
}

 // классы продукции. пока в них хранится только русская локализация
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

// массивы key-value для обращения к классам продукции и русской локализации животных

$goods = array(
	BarnAnimalGoodMilk::getKey()=>new BarnAnimalGoodMilk(),
	BarnAnimalGoodEgg::getKey()=>new BarnAnimalGoodEgg()
);

$livestockNamesRu = array(
	BarnAnimalCow::getKey()=>BarnAnimalCow::getNameRu(),
	BarnAnimalChicken::getKey()=>BarnAnimalChicken::getNameRu()
);

$barns = array();

$farmYield = array(); // массив, куда собирается информация об общем сборе со всех хлевов
$farmLivestock = array();
$farmLivestockByType = array();

// в ТЗ сказано создать 1 хлев с 10 коровами и 20 курами
// реализация даёт возможным задавать необходимое количество хлевов, коров и кур

$BARNS_QUANTITY = 1;
// $BARNS_QUANTITY = rand(1, 5); // debug

for ($i = 0; $i < $BARNS_QUANTITY; $i++) {
	$barn = new Barn(); // создаём хлев
	array_push($barns, $barn);

	$cowQuantity = 10;
	// $cowQuantity = rand(5, 15); // debug
	$chickenQuantity = 20;
	// $chickenQuantity = rand(15, 25); // debug

	for ($i2 = 0; $i2 < $cowQuantity; $i2++) {
		$animal = new BarnAnimalCow(); // создаём и регистрируем корову
		$animal->registerAtBarn($barn);
	}
	for ($i2 = 0; $i2 < $chickenQuantity; $i2++) {
		$animal = new BarnAnimalChicken(); // создаём и регистрируем куру
		$animal->registerAtBarn($barn);
	}
}

foreach ($barns as $barn) {
	$barn->collectGoods(); // собираем продукцию
	
	// $collections = 1;
	// $collections = rand(1, 10); // debug
	// for ($i = 0; $i < $collections; $i++) {
		// $barn->collectGoods();
	// }
}

// отображаем итоговую статистику по всей ферме

echo "\n\nИтог сбора на ферме Дядюшки Боба. Кол-во хлевов: " . count($barns) . "\n";
echo "Всего животных на ферме (" . count($farmLivestock) . "): |";
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

