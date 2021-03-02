<?php
//==================================================
//Ферма OOP [main.php]
//Разработал Timur Moziev (https://github.com/TimurRin, code@timurrin.ru) (2021-02-26)
//>>>> Имплементация механизма фермы в object-oriented programming (OOP), тестовое задание для компании Oxem Studio
//==================================================

class Barn {
	private static $livestock = array(); // indexed-массив со всеми животными хлева
	private static $yieldByGood = array(); // в этот массив соберём информацию о собранной продукции
	private static $animalTypes = array(); // в этот key-value массив автоматически добавляются все производные от BarnAnimal классы (key = string classname), для валидации. значение (value = int) используется в регистрационных номерах животных

	// метод регистрирует разрешённый тип животного в хлеве, увеличивает значение для рег-номера и возвращает его для формирования рег-номера
	public static function updateAnimalTypeID($animalClass) {
		if (!isset(self::$animalTypes[$animalClass])) {
			self::$animalTypes[$animalClass] = 0;
		}
		self::$animalTypes[$animalClass]++; // регистрируем класс и обновляем значение рег-номера по типу животного
		return self::$animalTypes[$animalClass];
	}

	// метод регистрирует животное в хлеве
	public static function registerAnimal($animal) {
		$animalClass = get_class($animal);
		if (isset(self::$animalTypes[$animalClass])) {
			array_push(self::$livestock, $animal); // добавляем в массив хлева
		} else {
			echo "Класс '" . $animalClass . "' нельзя добавлять в хлев. Допускаются только производные от класса BarnAnimal\n";
		}
	}

	// метод собирает ресурсы с хлева
	public static function collectGoods() {
		foreach (self::$livestock as $animal) {
			$goodClass = $animal->getGood(); // получаем тип продукции животного
			$yield = $animal->getGoodYield(); // получаем количество продукции с каждого животного

			// записываем сбор в хлевную статистику
			if (!isset(self::$yieldByGood[$goodClass])) {
				self::$yieldByGood[$goodClass] = 0;
			}
			self::$yieldByGood[$goodClass] += $yield;
		}
	}

	// метод отображает статистику собранного раннее
	public static function displayGoods() {
		echo "\nСписок продукции из хлева:\n";
		foreach (self::$yieldByGood as $goodClass => $yield) {
			echo $goodClass::getName() . " - " . $yield . "" . $goodClass::getUnit() . "\n";
		}
	}
}

// абстрактный класс для животных, на него основе создаём класс коровы и куры
abstract class BarnAnimal {
	private $id; // здесь хранится регистрационный номер

	public function __construct() {
		$id = Barn::updateAnimalTypeID(get_called_class());

		// присваиваем регистрационный номер животному
		$this->id = $this->getKey() . "_" . strval($id);
	}

	abstract public static function getKey(); // для красивого регистрационного номера
	// abstract public static function getTypeName(); // для красивого отображения типа животного
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

	// public static function getTypeName() {
		// return "Корова";
	// }

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

	// public static function getTypeName() {
		// return "Кура";
	// }

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
	abstract public function getUnit();

    public function __toString()
    {
        return $this->getName();
    }
}

class BarnAnimalGoodMilk extends BarnAnimalGood {
	public function getName() {
		return "Молоко";
	}

	public function getUnit() {
		return " л.";
	}
}

class BarnAnimalGoodEgg extends BarnAnimalGood {
	public function getName() {
		return "Яйцо";
	}

	public function getUnit() {
		return " шт.";
	}
}

// в ТЗ сказано создать хлев с 10 коровами и 20 курами

$cowQuantity = 10;
$chickenQuantity = 20;

// создаём и регистрируем животных
for ($i2 = 0; $i2 < $cowQuantity; $i2++) {
	$animal = new BarnAnimalCow();
	Barn::registerAnimal($animal);
}
for ($i2 = 0; $i2 < $chickenQuantity; $i2++) {
	$animal = new BarnAnimalChicken();
	Barn::registerAnimal($animal);
}

Barn::collectGoods(); // собираем продукцию
Barn::displayGoods(); // отображаем собранную продукцию

?>

