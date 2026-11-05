# Smartart Weekly Timetable

[English](README.md) | [Magyar](README.hu.md)

Reszponzív, színkódolt heti órarend WordPress-weboldalakhoz, Google Táblázat (Google Sheets) adatforrással.

Kezeld az edzéseket, órákat, edzőket, műszakokat vagy időpontokat egy Google Táblázatban, és jelenítsd meg a beosztást WordPress-weboldaladon egyetlen shortcode segítségével. A táblázat módosítása után nincs szükség fájlok exportálására vagy újbóli feltöltésére.

## Funkciók

- **Google Táblázat alapú tartalomkezelés:** a beosztás egy ismerős táblázatban szerkeszthető.
- **Automatikus frissítés:** a weboldal a gyorsítótár lejártakor tölti le a közzétett CSV-adatokat.
- **Reszponzív megjelenés:** asztali gépen heti táblázat, mobilon napválasztó fülek és napi lista jelenik meg.
- **Személyenkénti színkódolás:** minden edző, oktató vagy munkatárs saját színt kap.
- **Automatikus időblokk-összevonás:** az egymást követő, azonos személyhez rendelt félórás idősávok egyetlen blokkban jelennek meg.
- **Rugalmas órarend:** hétfőtől szombatig, tetszőleges időtartományban, félórás idősávokkal.
- **Szerveroldali gyorsítótárazás:** csökkenti a Google Sheets felé küldött ismételt kérések számát, és segít gyorsan tartani az oldalbetöltést.
- **Utolsó sikeres adatmásolat megőrzése:** ha a Google Sheets átmenetileg nem érhető el, a legutóbb sikeresen letöltött órarend továbbra is megjeleníthető.
- **Akadálymentes működés:** szemantikus HTML-táblázat és billentyűzettel kezelhető mobilos fülek; az órarend JavaScript nélkül is működik.
- **Arculathoz igazítható megjelenés:** a színek, betűtípusok és lekerekítések CSS-változókkal módosíthatók.
- **Könnyű felépítés:** nem hoz létre saját adatbázistáblákat, nem használ látogatói követést vagy sütiket. Az ideiglenes órarend-cache a WordPress transients rendszerében tárolódik.
- **Fordításra előkészítve:** magyar nyelvet is tartalmaz.

## Követelmények

- WordPress 6.3 vagy újabb verzió
- PHP 8.0 vagy újabb verzió
- CSV formátumban közzétett Google Táblázat

## Telepítés

1. Töltsd fel a bővítmény mappáját a `/wp-content/plugins/` könyvtárba, vagy telepítsd a WordPress adminfelületén a **Bővítmények → Új hozzáadása** menüpontból.
2. Aktiváld a **Smartart Weekly Timetable** bővítményt.
3. Készítsd el az órarendet a Google Táblázatokban az alább ismertetett szerkezet szerint.
4. A Google Táblázatokban válaszd a **Fájl → Megosztás → Közzététel a weben** menüpontot.
5. Válaszd ki az órarendet tartalmazó munkalapot, majd formátumként a **Vesszővel tagolt értékek (.csv)** lehetőséget. Tedd közzé a táblázatot, és másold ki a létrejött hivatkozást.
6. A WordPress-oldalon adj hozzá egy **Shortcode** blokkot vagy az oldalépítőd megfelelő shortcode widgetjét.
7. Illeszd be az alábbi shortcode-ot, és cseréld ki a példa URL-t a saját, közzétett CSV-hivatkozásodra.

```text
[smartart_timetable url="https://docs.google.com/spreadsheets/d/e/YOUR-ID/pub?gid=0&single=true&output=csv"]
```

A shortcode használható a WordPress blokkszerkesztőjében, Elementorban és más, shortcode-okat támogató oldalszerkesztőkben is.

## A Google Táblázat felépítése

Hozz létre egy Google Táblázatot az alábbi szerkezettel, az A–G oszlopok használatával:

| Cellatartomány | Tartalom |
|---|---|
| `A2` | A hét megnevezése, például `2026. november 2–7.` |
| `B4:G4` | Legfeljebb hat edző, oktató vagy munkatárs neve |
| `B6:G6` | A hét napjai hétfőtől szombatig. Egy nap nevének törlésével az adott nap elrejthető. |
| `B7:G7` | A napok neve alatt megjelenő dátumok |
| `A8:A…` | Idősávok soronként, például `8:00 – 8:30` |
| `B8:G…` | Az adott idősávban foglalkozást tartó személy neve |

A 4. sorban elfoglalt oszloppozíció határozza meg az adott személy színét: a B oszlop az 1. színt, a C oszlop a 2. színt kapja, és így tovább.

**Tipp:** az órarend celláihoz érdemes legördülő listát beállítani, amelynek értékei a `B4:G4` tartományból származnak. Így elkerülhetők a névelírások és az eltérő névváltozatok.

A hét megnevezése és a dátumok képletekkel is előállíthatók, így ezek automatikusan frissülhetnek.

## Shortcode és beállításai

A kötelező `url` attribútumnak a közzétett Google Táblázat CSV-hivatkozását kell tartalmaznia:

```text
[smartart_timetable url="YOUR_PUBLISHED_CSV_URL"]
```

A választható `cache` attribútummal megadható, hány percig tárolja a bővítmény a letöltött órarendet.

```text
[smartart_timetable url="YOUR_PUBLISHED_CSV_URL" cache="10"]
```

- Minimum: `1` perc
- Maximum: `1440` perc
- Alapértelmezett érték: `5` perc

A Google-nak néhány percre szüksége lehet a közzétett táblázat frissítéséhez. Az alapértelmezett gyorsítótárazással számolva általában 5–10 percet érdemes várni, mire a módosítás megjelenik a weboldalon.

## Megjelenés testreszabása

Az órarend CSS-változókat használ, ezért könnyen hozzáigazítható a weboldal arculatához. A CSS-kódot a sablon **Egyedi CSS / További CSS** részébe vagy a child theme stíluslapjába illesztheted.

Példa:

```css
.sawt {
    --sawt-primary: #1d6fa5;
    --sawt-c0: #cfe8ff;
    --sawt-c1: #ffe0b8;
    --sawt-font-heading: "Montserrat", sans-serif;
}
```

Elérhető CSS-változók:

- `--sawt-primary`
- `--sawt-primary-light`
- `--sawt-heading`
- `--sawt-body`
- `--sawt-border`
- `--sawt-line`
- `--sawt-soft`
- `--sawt-bg`
- `--sawt-radius`
- `--sawt-row`
- `--sawt-font-heading`
- `--sawt-c0`–`--sawt-c5`: az egyes személyek színei

## Gyakori kérdések

### Az órarend helyett „The timetable is currently unavailable” üzenet jelenik meg. Mit ellenőrizzek?

Lépj be szerkesztőként, majd nyisd meg az érintett oldalt. A szerkesztők a konkrét hiba okát is láthatják.

A leggyakoribb okok:

- A táblázat CSV helyett weboldalként lett közzétéve.
- A forrásfájl Excel `.xlsx` fájl, nem valódi Google Táblázat. Nyisd meg a Google Táblázatokban, majd válaszd a **Fájl → Mentés Google Táblázatokként** lehetőséget.
- A közzétett hivatkozás hibás, vagy nem CSV-adatokat ad vissza.

### Milyen gyorsan jelennek meg a módosítások a weboldalon?

A Google néhány percen belül frissítheti a közzétett táblázatot. A bővítmény alapértelmezés szerint 5 percig gyorsítótárazza a letöltött adatokat. Általában 5–10 percet érdemes várni a módosítások megjelenésére. A gyorsítótárazás időtartama a `cache` shortcode-attribútummal módosítható.

### Használható Elementorral vagy más oldalszerkesztővel?

Igen. Illeszd be a shortcode-ot az oldalépítőd shortcode blokkba vagy widgetjébe.

### Módosíthatók a színek és a betűtípusok?

Igen. A sablon CSS-kódjában felülírhatod a **Megjelenés testreszabása** fejezetben felsorolt CSS-változókat.

### Milyen URL-eket fogad el a bővítmény?

Biztonsági okokból kizárólag olyan `https://docs.google.com/spreadsheets/...` hivatkozásokat fogad el, amelyek CSV-adatokat szolgáltatnak.

### Létrehoz adatbázistáblákat vagy használ sütiket a bővítmény?

A bővítmény nem hoz létre saját adatbázistáblákat, nem használ látogatói követést vagy sütiket. Az ideiglenes órarendadatokat a WordPress transients rendszerében tárolja gyorsítótárként, amelyet a WordPress kezel.

### Hogyan fordítható le a bővítmény?

A bővítmény fordításra előkészített, magyar nyelvi fordítást is tartalmaz. A text domain neve: `smartart-weekly-timetable`.

A fordítások a [WordPress fordítási felületén](https://translate.wordpress.org/projects/wp-plugins/smartart-weekly-timetable/) kezelhetők. Offline fordításhoz a `languages/smartart-weekly-timetable.pot` fájl használható sablonként.

## Külső szolgáltatás: Google Sheets

A bővítmény a weboldal adminisztrátora által közzétett Google Táblázat CSV-változatát tölti le.

- **Mire használja?** A közzétett CSV-órarend letöltésére, hogy az megjeleníthető legyen a weboldalon.
- **Mikor történik kapcsolatfelvétel?** A weboldal szervere akkor kéri le a shortcode-ban megadott CSV-hivatkozást, amikor a gyorsítótárazott példány lejár.
- **Milyen adatok kerülnek továbbításra?** A kérést a weboldal szervere küldi. A bővítmény nem továbbít látogatói adatokat a Google részére; a Google a szokásos szerverkérés adatait, például a szerver IP-címét látja.
- **Szolgáltató:** Google LLC.
- **Felhasználási feltételek:** https://policies.google.com/terms
- **Adatvédelmi irányelvek:** https://policies.google.com/privacy

## Támogatás és projektlinkek

- Weboldal: https://smartart.hu
- WordPress bővítmény adatlapja: https://wordpress.org/plugins/smartart-weekly-timetable/
- Fordítási projekt: https://translate.wordpress.org/projects/wp-plugins/smartart-weekly-timetable/

## Licenc

A Smartart Weekly Timetable a GNU General Public License 2-es vagy újabb verziója (GPL-2.0-or-later) alapján érhető el.
