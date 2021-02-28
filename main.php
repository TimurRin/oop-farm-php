<?php
//==================================================
//Ферма OOP [main.php]
//Разработал Timur Moziev (https://github.com/TimurRin, code@timurrin.ru) (2021-02-26)
//>>>> Имплементация механизма фермы в object-oriented programming (OOP), тестовое задание для компании Oxem Studio
//==================================================

class Farm {
	// Ферма у нас одна, поэтому можно не присваивать уникальный ID

	public $goods = array();

	public $barns = array(); // инстансы всех хлевов

	public $totalYield = array(); // массив, куда собирается информация об общем сборе со всех хлевов
	public $livestockByType = array(); // key-value массив, где key -- тип животного, value -- количество

	public function __construct($barnsQuantity) {
		// в ТЗ сказано создать 1 хлев с 10 коровами и 20 курами
		// реализация даёт возможным задавать необходимое количество хлевов, коров и кур

		for ($i = 0; $i < $barnsQuantity; $i++) {
			$barn = new Barn(); // создаём хлев
			$this->registerBarn($barn);

			$cowQuantity = 10;
			// $cowQuantity = rand(5, 15); // debug
			$chickenQuantity = 20;
			// $chickenQuantity = rand(15, 25); // debug

			for ($i2 = 0; $i2 < $cowQuantity; $i2++) {
				$animal = new BarnAnimalCow(); // создаём и регистрируем корову
				$barn->registerAnimal($animal);
			}
			for ($i2 = 0; $i2 < $chickenQuantity; $i2++) {
				$animal = new BarnAnimalChicken(); // создаём и регистрируем куру
				$barn->registerAnimal($animal);
			}
		}
	}

	// функция регистрирует хлев на этой ферме
	public function registerBarn($barn) {
		array_push($this->barns, $barn);

		$barn->farm = $this;

		echo "Мы зарегистрировали хлев (" . $barn . ") на ферме Дядюшки Боба\n";
	}

	public function launchGoodsCollection() {
		global $livestockNamesRu; // для красвого вывода на русском языке

		foreach ($this->barns as $barn) {
			$barn->collectGoods(); // собираем продукцию
		}
		// отображаем итоговую статистику по всей ферме

		echo "\n\nИтог сбора на ферме Дядюшки Боба. Кол-во хлевов: " . count($this->barns) . "\n";
		echo "Животные на ферме: |";
		$totalAnimals = 0;
		foreach ($this->livestockByType as $animalKey => $quantity) {
			$totalAnimals += $quantity;
			echo $livestockNamesRu[$animalKey] . " - " . $quantity . "|";
		}
		echo "[Всего: " . $totalAnimals . "]|";
		echo "\nСобранные с них ресурсы: |";
		foreach ($this->totalYield as $goodKey => $yield) {
			echo $this->goods[$goodKey]->getNameRu() . " - " . $yield . "" . $this->goods[$goodKey]->getUnitRu() . "|";
		}
		echo "\n";

		echo "\n\n";
	}
}

class Barn {
	public static $quantity = 0; // количество хлевов для образования уникального ID
	public $id; // регистрационный номер хлева

	public $farm; // ферма, в которой состоит хлев

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

	// функция регистрирует животное в этом хлеве
	public function registerAnimal($animal) {
		array_push($this->livestock, $animal); // добавляем в массив хлева

		$animalKey = $animal->getKey();

		if (!isset($this->livestockByType[$animalKey])) {
			$this->livestockByType[$animalKey] = 0;
		}
		$this->livestockByType[$animalKey] += 1;

		$animalKey = $animal->getKey();
		if (!isset($this->farm->livestockByType[$animalKey])) {
			$this->farm->livestockByType[$animalKey] = 0;
		}
		$this->farm->livestockByType[$animalKey] += 1;

		$animal->barn = $this;

		echo "Мы зарегистрировали животное (" . $animal->getNameRu() . ", ". $animal .") в хлев " . $animal->barn . "\n";
	}

	// Функция собирает ресурсы с данного хлева
	public function collectGoods() {
		global $livestockNamesRu; // для красвого вывода на русском языке

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

			if (!isset($this->farm->totalYield[$goodKey])) {
				$this->farm->totalYield[$goodKey] = 0;
			}
			$this->farm->totalYield[$goodKey] += $yield;
		}

		// отображаем статистику хлева

		// echo "\nМы пошли собирать продукцию из хлева " . $this . " в " . $this->collections . "-й раз (всего " . self::$collectionsTotal . ")\n";
		echo "\nМы пошли собирать продукцию из хлева " . $this . " (сбор №" . self::$collectionsTotal . ")";
		echo "\nЖивотных в хлеву: |";
		$totalAnimals = 0;
		foreach ($this->livestockByType as $animalKey => $quantity) {
			$totalAnimals += $quantity;
			echo $livestockNamesRu[$animalKey] . " - " . $quantity . "|";
		}
		echo "[Всего: " . $totalAnimals . "]|";
		echo "\nСписок собранного: |";
		foreach ($currentYield as $goodKey => $yield) {
			echo $this->farm->goods[$goodKey]->getNameRu() . " - " . $yield . "" . $this->farm->goods[$goodKey]->getUnitRu() . "|";
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

	abstract public static function getKey();
	abstract public static function getNameRu();
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

	public static function getNameRu() {
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

	public static function getNameRu() {
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

$livestockNamesRu = array(
	BarnAnimalCow::getKey()=>BarnAnimalCow::getNameRu(),
	BarnAnimalChicken::getKey()=>BarnAnimalChicken::getNameRu()
);

$farm = new Farm(1);

$farm->goods = array(
	BarnAnimalGoodMilk::getKey()=>new BarnAnimalGoodMilk(),
	BarnAnimalGoodEgg::getKey()=>new BarnAnimalGoodEgg()
);

$farm->launchGoodsCollection();
?>

