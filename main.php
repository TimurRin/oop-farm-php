<?php
//==================================================
//Ферма OOP [main.php]
//Разработал Timur Moziev (https://github.com/TimurRin, code@timurrin.ru) (2021-02-26)
//>>>> Имплементация механизма фермы в object-oriented programming (OOP), тестовое задание для компании Oxem Studio
//==================================================

class Farm {
	public static $quantity = 0; // количество ферм для образования уникального ID
	public $id; // регистрационный номер фермы
	public $name; // название фермы

	public $barns = array(); // инстансы всех хлевов

	public $totalYield = array(); // массив, куда собирается информация об общем сборе со всех хлевов
	public $livestockByType = array(); // key-value массив, где key -- тип животного, value -- количество

	public function __construct($name, $barnsQuantity) {
		$this->name = $name;
		// Несмотря на то, что ТЗ предусматривает лишь одну ферму, мы всё равно делаем ей отдельный класс и присваиваем идентификатор
		// Реализация на данный момент поддерживает несколько ферм
		$this->id = "farm_" . strval(self::$quantity);
		self::$quantity += 1;

		echo "Мы приобрели ферму " . $this . " (" . $this->getName() . ")\n";

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

	public function getName() {
		return $this->name;
	}

	// функция регистрирует хлев на этой ферме
	public function registerBarn($barn) {
		array_push($this->barns, $barn);

		$barn->farm = $this;

		echo "Мы зарегистрировали хлев (" . $barn . ") на ферме " . $this . " (" . $this->getName() . ")\n";
	}

	public function launchGoodsCollection() {
		foreach ($this->barns as $barn) {
			$barn->collectGoods(); // собираем продукцию
		}
		// отображаем итоговую статистику по всей ферме

		echo "\n\nИтог сбора на ферме " . $this . " (" . $this->getName() . "). Кол-во хлевов: " . count($this->barns) . "\n";
		echo "Животные на ферме: |";
		$totalAnimals = 0;
		foreach ($this->livestockByType as $animalClass => $quantity) {
			$totalAnimals += $quantity;
			echo $animalClass::getTypeName() . " - " . $quantity . "|";
		}
		echo "[Всего: " . $totalAnimals . "]|";
		echo "\nСобранные с них ресурсы: |";
		foreach ($this->totalYield as $goodClass => $yield) {
			echo $goodClass::getName() . " - " . $yield . "" . $goodClass::getUnitRu() . "|";
		}
		echo "\n";

		echo "\n\n";
	}

    public function __toString()
    {
        return $this->id;
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
		// echo "Мы приобрели хлев и дали ему регистрационный номер " . $this->id . "\n";
	}

	// функция регистрирует животное в этом хлеве
	public function registerAnimal($animal) {
		array_push($this->livestock, $animal); // добавляем в массив хлева

		$animalClass = get_class($animal);

		if (!isset($this->livestockByType[$animalClass])) {
			$this->livestockByType[$animalClass] = 0;
		}
		$this->livestockByType[$animalClass] += 1;

		if (!isset($this->farm->livestockByType[$animalClass])) {
			$this->farm->livestockByType[$animalClass] = 0;
		}
		$this->farm->livestockByType[$animalClass] += 1;

		$animal->barn = $this;

		echo "Мы зарегистрировали животное (" . $animal->getTypeName() . ", ". $animal .") в хлев " . $animal->barn . " на ферме " . $this->farm . " (" . $this->farm->getName() . ")\n";
	}

	// Функция собирает ресурсы с данного хлева
	public function collectGoods() {
		self::$collectionsTotal += 1;
		// $this->collections += 1;

		$currentYield = array(); // в этот массив соберём информацию о ресурсах с этого хлева для показа в конце функции

		foreach ($this->livestock as $animal) {
			$goodClass = $animal->getGood();

			$yield = $animal->getGoodYield(); // получаем количество продукции с каждого животного

			// записываем в хлевную и общефермерскую статистику

			if (!isset($currentYield[$goodClass])) {
				$currentYield[$goodClass] = 0;
			}
			$currentYield[$goodClass] += $yield;

			if (!isset($this->farm->totalYield[$goodClass])) {
				$this->farm->totalYield[$goodClass] = 0;
			}
			$this->farm->totalYield[$goodClass] += $yield;
		}

		// отображаем статистику хлева

		// echo "\nМы пошли собирать продукцию из хлева " . $this . " в " . $this->collections . "-й раз (всего " . self::$collectionsTotal . ")\n";
		echo "\nМы пошли собирать продукцию из хлева " . $this . " (сбор №" . self::$collectionsTotal . ")";
		echo "\nЖивотных в хлеву: |";
		$totalAnimals = 0;
		foreach ($this->livestockByType as $animalClass => $quantity) {
			$totalAnimals += $quantity;
			echo $animalClass::getTypeName() . " - " . $quantity . "|";
		}
		echo "[Всего: " . $totalAnimals . "]|";
		echo "\nСписок собранного: |";
		foreach ($currentYield as $goodClass => $yield) {
			echo $goodClass::getName() . " - " . $yield . "" . $goodClass::getUnitRu() . "|";
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
		// echo "Мы приобрели животное (". $this->getTypeName() .") и дали ему регистрационный номер " . $this->id . "\n";
	}

	abstract public static function getKey(); // для красивого идентификатора
	abstract public static function getTypeName(); // для красивого отображения типа животного
	abstract public function getGood(); // получаем тип продукции
	abstract public function getGoodYield(); // получаем количество возможной продукции за раз

    public function __toString()
    {
        return $this->id;
    }
}

class BarnAnimalCow extends BarnAnimal {
	public static function getKey() {
		return "cow";
	}

	public static function getTypeName() {
		return "Корова";
	}

	public function getGood() {
		return "BarnAnimalGoodMilk";
	}

	public function getGoodYield() {
		return rand(8, 12);
	}
}

class BarnAnimalChicken extends BarnAnimal {
	public static function getKey() {
		return "chicken";
	}

	public static function getTypeName() {
		return "Кура";
	}

	public function getGood() {
		return "BarnAnimalGoodEgg";
	}

	public function getGoodYield() {
		return rand(0, 1);
	}
}

 // классы продукции. пока в них хранится только русская локализация
abstract class BarnAnimalGood {
	abstract public function getName();
	abstract public function getUnitRu();

    public function __toString()
    {
        return $this->getName();
    }
}

class BarnAnimalGoodMilk extends BarnAnimalGood {
	public function getName() {
		return "Молоко";
	}

	public function getUnitRu() {
		return " л.";
	}
}

class BarnAnimalGoodEgg extends BarnAnimalGood {
	public function getName() {
		return "Яйцо";
	}

	public function getUnitRu() {
		return " шт.";
	}
}

echo "\n\n";

$farm = new Farm("Ферма Дядюшки Боба", 1); // создаём ферму с одним хлевом
// $farm = new Farm("The Random Farm", rand(1,5)); // debug

$farm->launchGoodsCollection(); // собираем ресурсы
?>

