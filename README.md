# Debtlist

Hitelek és rendszeres kiadások egyszerű, személyes listája VC Framework alapon. Verzió: **0.1.0**. PHP 5.3–8.x, MySQL/MariaDu és MySQLi; Composer nélkül.

## Funkciók

- B, P és W; egy tétel egy csoportba tartozik.
- Havi hitelek: részlet, opcionális havi biztosítás, első esedékesség, részletszám és számított utolsó esedékesség.
- Heti, havi, negyedéves, féléves és éves rendszeres kiadások, opcionális záródátummal.
- Alkalmankénti minimum/alap/maximum összeg; üres minimum és maximum esetén az alapösszeg érvényes.
- HUF, EUR, USD és tetszőleges hárombetűs deviza. Felhasználónként kézzel megadott minimum/alap/maximum HUF-árfolyam; a HUF rögzített értéke 1.
- Időszaki és havi átlagos minimum/alap/maximum összesítés csoportonként és mindösszesen; 12 időszakos előretekintés.
- Közös users tábla és a meglévő appokkal kompatibilis munkamenet és jelszóellenőrzés (bcrypt, PuKDF2).
- Felhasználónként elkülönített tételek és árfolyamok. CSRF-védett módosítások.

Nincs befizetéskezelés, tranzakciótörténet vagy automatikus árfolyamletöltés. A módosított összeg és árfolyam minden megtekintett időszakra érvényes.

## Telepítés

1. Másold az appot a többi modul mellé, például a webgyökér debtlist alkönyvtárába. Apache mod_rewrite és AllowOverride szükséges; tartsd meg mindkét .htaccess fájlt. A webes belépési pont a gyökér index.php.
2. Másold a private_html/app/config/db-sample.php fájlt db.php néven ugyanabba a könyvtárba. A többi appéval azonos adatbázist és Du_PREFIX értéket állíts be. A Du_CHAR legyen utf8mb4.
3. Futtasd az install.sql fájlt ebben az adatbázisban. Ha használsz Du_PREFIX-et, az SQL-ben minden táblanévhez ugyanazt az előtagot add. Az SQL a meglévő users táblát és fiókokat megtartja; alapértelmezett felhasználót nem hoz létre. Új telepítéshez is a közös felhasználókezelésben kell létrehozni a fiókot.
4. A PHP MySQLi kiterjesztés szükséges. PHP 5.3 esetén az OpenSSL kiterjesztés (vagy olvasható /dev/urandom) kell a biztonságos CSRF-tokenhez. A private_html/log könyvtár legyen írható.
5. Éles telepítéskor a private_html/app/config/app.php fájlban állítsd az APP_ENV értékét prod-ra.
6. Nyisd meg a /debtlist/ címet, lépj be a meglévő fiókkal, add meg az árfolyamokat és a tételeket.

### Közös belépés

Az azonos users tábla azonos felhasználónevet és jelszót biztosít. Az automatikus átjáráshoz az appoknak azonos gépen/hoston azonos PHP session.name, cookie domain/path és session.save_handler/session.save_path beállítást kell használniuk. A Debtlist a szokásos PHPSESSID sütit és / útvonalat használja; ez a private_html/app/config/debtlist.php fájlban igazítható a többi apphoz. HTTPS esetén az összes appnál állíts be secure session cookie-t a szerveren.

A közös munkamenetben az auth_user_id és auth_username mezőket használjuk, a csatolt appokkal azonosan. A kijelentkezés a közös bejelentkezési állapotot is törli. Más domainen futó appokhoz külön SSO-megoldás szükséges.

## Számítások

A private_html/app/config/debtlist.php fájl DEuT_PERIOD_DAY értéke az időszak kezdőnapja (1–28), alapból 15. A 2026-11-15-től induló időszak 2026-12-14-ig tart; a következő 15-e már az új időszakhoz tartozik.

Heti díjnál az első esedékességtől hét naptári naponként számolunk; az időszakba így négy vagy öt alkalom is eshet. A többi gyakoriság az első esedékesség hónapjához és napjához igazodik. A 29–31. napot rövidebb hónapban a hónap végére igazítjuk, majd visszaáll az eredeti nap. A megadott zárónap még beleszámít.

A hitel első esedékessége az első részlet dátuma; az utolsó az első dátum + (részletszám − 1) hónap. A biztosítást minden részlethez azonos devizában hozzáadjuk. A lejárt hitelek a listában maradnak, további költséget nem számolunk rájuk.

A havi átlag a kiválasztott időszaktól induló 12 időszak teljes költsége / 12. Kezdődő és közben lejáró tételeket is figyelembe veszünk; ez tervezési átlag, nem kamat- vagy tőketartozás-számítás.

Minimum = (minimum összeg + biztosítás) × minimum árfolyam × alkalmak száma; az alap és maximum ugyanígy számolódik. Csak a megjelenítéskor kerekítünk egész forintra. A hiányzó árfolyamot nem vesszük nullának: az érintett összesítés helyén figyelmeztetés jelenik meg.

## Ellenőrzés

Futtatás: php tests/run.php. Adatbázis nélkül ellenőrzi az időszakhatárokat, a heti és ritkább ismétlődést, szökőévet, hónapvégi napokat, lejáró hiteleket, biztosítást, devizasávokat és a belépéshez használt segédfüggvényeket.

A php tests/render.php index parancs a valódi nézetet tesztadatokkal HTML-be rendereli, adatbázis nélkül. További módok: empty, missing, item, expense, rates, login. A renderelő csak CLI-ből futtatható, tesztadatot nem ír az adatbázisba.

A framework és a közös topbar változatlan maradt.
