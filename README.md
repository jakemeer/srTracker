# srTracker
Ett enkelt API som �r till f�r att h�mta information om nyinkomna produkter hos Swedrock fr�n deras webbshop (http://www.swedrock.se/). 
Informationen som tillsammans utg�r en produkt �r:
- produktens titel
- url till produkten hos Swedrock.se
- src fr�n produktens tillh�rande img

# Funktionalitet
H�mta alla nya CD's
H�mta alla nya LP's
H�mta alla nya Tshirt's
H�mta alla nya Longsleeves's
H�mta alla nya Munktr�jor

# Hur f�r man tillg�ng till API:et?
Det finns publikt p� GitHub och best�r av en php-fil som man anv�nder med hj�lp av require_once('srTracker.php');

# Metoder
Det finns huvudsakligen en metod som h�mtar information: getAll($item). 

Till�tna $items �r (str�ngar):
cd
lp
ls
ts
hd

Metoden returnerar en array.

# Andra api:er
cURL anv�nds f�r att h�mta data fr�n Swedrock.se

# Exempel
require_once('srTracker.php');

$sr = new srTracker('host', 'username', 'password', 'database');

$cd = $sr->getAll('cd'); 

$lp = $sr->getAll('lp'); 

$tshirts = $sr->getAll('ts'); 

$longsleeves = $sr->getAll('ls'); 

$hoods = $sr->getAll('hd');

print_r($tshirts);

Array ( 
	[0] => Array ( 
		[title] => Watain - Lawless Fire [TS] 
		[url] => http://www.swedrock.se/klader/t-shirt/watain-lawless-fire-ts-1.html 
		[img] => http://www.swedrock.se/shop/thumbnails/shop/23473/art73/h1949/14171949-origpic-f53189.jpg_0_3.33333_100_93.3333_119_111_75.jpg 
	) 
	[1] => Array ( 
		[title] => Behexen - Nightside Emanations [TS] 
		[url] => http://www.swedrock.se/klader/t-shirt/behexen-nightside-emanations-ts.html 
		[img] => http://www.swedrock.se/shop/thumbnails/shop/23473/art73/h6858/14106858-origpic-86e6d4.jpg_26.41_0_47.18_100_119_111_75.jpg 
	) 
	[2] => Array ( 
		[title] => Setherial - Enemy Of Creation [TS] 
		[url] => http://www.swedrock.se/klader/t-shirt/setherial-enemy-of-creation-ts.html 
		[img] => http://www.swedrock.se/shop/thumbnails/shop/23473/art73/h6847/14106847-origpic-ded729.jpg_26.41_0_47.18_100_119_111_75.jpg 
	) 
) 

# Felhantering
Om inget resultat erh�lls eller om n�got annat g�r fel kastas ett undantag, se exempel nedan (med felaktigt username).

try {
   $sr = new srTracker('host', 'WRONGusername', 'password', 'database');

} catch (Exception $e) {
   print $e->getMessage(); //Will print 'Wrong parameter'
}