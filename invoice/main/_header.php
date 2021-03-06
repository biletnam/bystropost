<?php

	# Подключаем классы
	if (!isset($class)) { $class = 'WMXI'; }
	require_once(getcwd()."/invoice/$class.php");


	# Режим отладки с сохранением промежуточных результатов в файл
	define('WMXI_LOG', 'wmxi.log');

	# Форсирование использования библиотек
	# define('WMXI_MATH', 'bcmath4'); # Варианты: gmp, bcmath4, bcmath5
	# define('WMXI_MD4', 'hash'); # Варианты: mhash, hash, class


	# Создаём объект класса. Передаваемые параметры:
	# - путь к сертификату, используемому для защиты от атаки с подменой ДНС
	# - кодировка, используемая на сайте. По умолчанию используется UTF-8
	$wmxi = new $class(getcwd()."/invoice/WMXI.crt", 'UTF-8');

	
	#/*

	# Инициализация с помощью резервной копии файла ключей
	# от Webmoney Keeper Classic. Передаваемые параметры:
	# - WMID - идентификатор пользователя
	# - пароль пользователя от резервной копии файла ключей
	# - путь к резервной копии файла ключей (обычно размером 164 байта)
	# - бинарное содержимое файла ключей
	# - мантисса и экспонента

	# Параметры инициализации ключем Webmoney Keeper Classic
	define('WMID', '915236488902');
	define('PASS', '32233221');
	define('KWMFILE', getcwd()."/invoice/keys/915236488902.kwm");
	# define('KWMDATA', base64_decode(file_get_contents(KWMFILE.'.base64')));
	# define('EKEY', file_get_contents(KWMFILE.'.ekey'));
	# define('NKEY', file_get_contents(KWMFILE.'.nkey'));

	if (defined('EKEY') && defined('NKEY')) { $wmkey = array('ekey' => EKEY, 'nkey' => NKEY); }
	elseif (defined('KWMDATA')) { $wmkey = array('pass' => PASS, 'data' => KWMDATA); }
	elseif (defined('KWMFILE')) { $wmkey = array('pass' => PASS, 'file' => KWMFILE); }
	if (isset($wmkey)) { $wmxi->Classic(WMID, $wmkey); }	
		
	/*/	

	# Инициализация с помощью сертификата
	# от Webmoney Keeper Light. Передаваемые параметры:
	# - путь к файлу сертификата
	# - путь к файлу приватного ключа
	# - пароль от приватного ключа

	# Параметры инициализации сертификатом Webmoney Keeper Light
	define('CER', realpath('../keys/000000000000.cer'));
	define('KEY', realpath('../keys/000000000000.key'));
	define('PASS', '11111111');
	
	if (defined('KEY') && defined('CER') && defined('PASS')) { $wmkey = array('key' => KEY, 'cer' => CER, 'pass' => PASS); }
	if (isset($wmkey)) { $wmxi->Light($wmkey); }

	#*/

	
	# Дополнительные настройки:

	# Локализация описаний ошибок. По умолчанию en_US, находятся в папке i18n и доступны для перевода.
	# define('WMXI_LOCALE', 'ru_RU');

	
	# Константы, используемые в примерах
	define('PRIMARY_WMID',   '915236488902');
	define('PRIMARY_PURSE', 'R135034329557');
	define('ANOTHER_WMID',   '288687149892');
	define('ANOTHER_PURSE', 'R258145967104');

	define('DATE_A', date('Ymd H:i:s', strtotime('-1 week')));
	define('DATE_B', date('Ymd H:i:s', strtotime('+1 day')));

?>